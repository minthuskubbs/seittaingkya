<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Treatment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'patient_id', 'appointment_id', 'doctor_id', 'procedure_id',
        'tooth', 'diagnosis', 'notes', 'treatment_date', 'doctor_feedback',
        // Billing
        'denture_charge', 'denture_type_id', 'surgery_charge', 'additional_charge',
        'extraction_price', 'extraction_qty', 'extraction_type_id',
        'implant_price', 'implant_qty', 'implant_type_id',
        'total_amount', 'discount_type', 'discount_value',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'denture_charge' => 'decimal:2',
        'surgery_charge' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'extraction_price' => 'decimal:2',
        'implant_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    public function extractionType(): BelongsTo
    {
        return $this->belongsTo(ToothChargeType::class, 'extraction_type_id');
    }

    public function implantType(): BelongsTo
    {
        return $this->belongsTo(ToothChargeType::class, 'implant_type_id');
    }

    public function dentureType(): BelongsTo
    {
        return $this->belongsTo(DentureType::class, 'denture_type_id');
    }

    public function treatmentTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TreatmentType::class)
            ->withPivot(['qty', 'unit_price', 'line_total']);
    }

    public function treatmentTypesTotal(): float
    {
        return (float) $this->treatmentTypes->sum(fn ($tt) => (float) $tt->pivot->line_total);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(TreatmentFee::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /** Medicine sales linked to this treatment (combined into its invoice). */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function extractionTotal(): float
    {
        return (float) $this->extraction_price * (int) $this->extraction_qty;
    }

    public function implantTotal(): float
    {
        return (float) $this->implant_price * (int) $this->implant_qty;
    }

    /**
     * Commissionable "Treatment Fees" total: treatment types + per-tooth + surgery
     * + additional + fees flagged as the treatment group. Excludes Services Fees,
     * denture and medicine (doctor commission is calculated on this).
     */
    public function treatmentFeesTotal(): float
    {
        return $this->treatmentTypesTotal()
            + $this->extractionTotal() + $this->implantTotal()
            + (float) $this->surgery_charge + (float) $this->additional_charge
            + (float) $this->fees()->where('fee_group', 'treatment')->sum('line_total');
    }

    /** Services Fees total (scanner, service charge, x-ray) — not commissionable. */
    public function servicesFeesTotal(): float
    {
        return (float) $this->fees()->where('fee_group', 'service')->sum('line_total');
    }

    /** Fees + treatment types + per-tooth + surgery + additional + denture (excludes linked medicine sales). */
    public function chargesTotal(): float
    {
        return (float) $this->fees()->sum('line_total')
            + $this->treatmentTypesTotal()
            + $this->extractionTotal() + $this->implantTotal()
            + (float) $this->surgery_charge + (float) $this->additional_charge + (float) $this->denture_charge;
    }

    /** Charges + linked medicine sales, BEFORE discount. */
    public function grossTotal(): float
    {
        return $this->chargesTotal() + (float) $this->sales()->sum('total');
    }

    /** Discount amount in MMK (percentage of gross, or a flat amount). */
    public function discountAmount(): float
    {
        $value = (float) $this->discount_value;
        if ($value <= 0) {
            return 0.0;
        }
        if ($this->discount_type === 'percent') {
            return round($this->grossTotal() * min($value, 100) / 100, 2);
        }

        return min($value, $this->grossTotal());
    }

    /** Grand total the patient pays: gross (charges + medicine) minus discount. */
    public function invoiceTotal(): float
    {
        return max(0, $this->grossTotal() - $this->discountAmount());
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return $this->invoiceTotal() - $this->paidAmount();
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->chargesTotal();
        $this->saveQuietly();
    }
}
