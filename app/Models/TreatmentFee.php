<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentFee extends Model
{
    protected $table = 'treatment_fee';

    protected $fillable = [
        'treatment_id', 'fee_id', 'name', 'category', 'fee_group', 'price', 'is_foc', 'quantity', 'line_total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'is_foc' => 'boolean',
    ];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }
}
