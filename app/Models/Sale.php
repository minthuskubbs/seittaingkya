<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'client_uuid', 'sale_no', 'sale_type', 'patient_id', 'treatment_id', 'doctor_id',
        'customer_name', 'subtotal', 'discount', 'total', 'created_by', 'sold_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public const TYPES = ['walk_in' => 'Walk In', 'doctor' => 'Doctor', 'other' => 'Other'];

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (empty($sale->sale_no)) {
                $sale->sale_no = 'S'.now()->format('ymd').str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            }
            $sale->sold_at ??= now();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
