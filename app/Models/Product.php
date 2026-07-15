<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'sku', 'type', 'unit', 'cost_price', 'sale_price',
        'stock_qty', 'low_stock_threshold', 'expiry_date', 'supplier_id', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->low_stock_threshold;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_qty', '<=', 'low_stock_threshold');
    }

    /** Adjust stock and record a movement in one place. */
    public function adjustStock(int $signedQty, string $type, ?string $reference = null, ?string $note = null): StockMovement
    {
        $this->stock_qty += $signedQty;
        $this->save();

        return $this->movements()->create([
            'clinic_id' => $this->clinic_id,
            'type' => $type,
            'quantity' => $signedQty,
            'balance_after' => $this->stock_qty,
            'reference' => $reference,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }
}
