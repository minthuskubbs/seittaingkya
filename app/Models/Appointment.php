<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Appointment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'client_uuid', 'appointment_no', 'patient_id', 'doctor_id',
        'scheduled_at', 'status', 'reason', 'parent_id', 'reminder_at', 'reminder_sent',
        'doctor_note', 'assistance_note', 'total_amount', 'denture_charge', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'total_amount' => 'decimal:2',
        'denture_charge' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $a) {
            if (empty($a->appointment_no)) {
                $a->appointment_no = 'A'.now()->format('ymd').str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'parent_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(Appointment::class, 'parent_id');
    }

    /** Snapshotted fee lines for this appointment. */
    public function fees(): HasMany
    {
        return $this->hasMany(AppointmentFee::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balance(): float
    {
        return (float) $this->total_amount - $this->paidAmount();
    }

    public function paymentStatus(): string
    {
        $paid = $this->paidAmount();
        if ($paid <= 0) {
            return $this->total_amount > 0 ? 'unpaid' : 'paid';
        }
        return $paid >= (float) $this->total_amount ? 'paid' : 'partial';
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->fees()->sum('line_total');
        $this->saveQuietly();
    }
}
