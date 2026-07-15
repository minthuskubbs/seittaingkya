<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use BelongsToClinic;

    protected $fillable = ['clinic_id', 'name', 'category', 'price', 'is_foc', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_foc' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Effective price honouring the FOC flag. */
    public function effectivePrice(): float
    {
        return $this->is_foc ? 0.0 : (float) $this->price;
    }

    public const CATEGORIES = [
        'service' => 'Patient Service Charge',
        'xray' => 'X-ray Charge',
        'scanner' => 'Scanner Fee',
        'dentist' => 'Dentist Fee',
        'other' => 'Other',
    ];
}
