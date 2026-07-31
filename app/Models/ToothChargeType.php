<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ToothChargeType extends Model
{
    protected $fillable = ['kind', 'name', 'price', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public const KINDS = [
        'extraction' => 'Tooth Extraction Type',
        'implant' => 'Tooth Implant Type',
    ];

    public function scopeKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
