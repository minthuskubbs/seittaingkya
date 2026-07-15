<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SaleType extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (SaleType $type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name, '_');
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** slug => name map used for dropdowns and labels. */
    public static function options(): array
    {
        return static::active()->pluck('name', 'slug')->all();
    }

    public static function labelFor(?string $slug): string
    {
        if (! $slug) {
            return '—';
        }

        return static::where('slug', $slug)->value('name')
            ?? Str::of($slug)->replace('_', ' ')->title();
    }
}
