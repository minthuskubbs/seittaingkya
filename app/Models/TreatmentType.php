<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TreatmentType extends Model
{
    protected $fillable = ['name', 'price', 'require_qty', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'require_qty' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(Treatment::class);
    }
}
