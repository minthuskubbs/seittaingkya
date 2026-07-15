<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $fillable = ['name', 'code', 'description', 'default_price', 'is_active'];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
