<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'category', 'title', 'amount', 'income_date', 'note', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];
}
