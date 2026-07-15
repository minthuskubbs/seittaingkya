<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'payable_type', 'payable_id', 'amount', 'method',
        'reference', 'paid_at', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'kbzpay' => 'KBZPay',
        'wavepay' => 'WavePay',
        'card' => 'Card',
        'bank' => 'Bank Transfer',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}
