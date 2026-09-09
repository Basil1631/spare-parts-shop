<?php

namespace App\Models;

use App\Support\Sku;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'sku_normalized',
        'price_fils',
        'vat_rate',
        'min_qty',
        'qty_on_hand',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_fils' => 'integer',
            'vat_rate' => 'decimal:2',
            'min_qty' => 'integer',
            'qty_on_hand' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->sku_normalized = Sku::normalize($product->sku);
        });
    }

    public function isLowStock(): bool
    {
        return $this->qty_on_hand <= $this->min_qty;
    }

    public function suggestedOrderQty(): int
    {
        return max(0, $this->min_qty - $this->qty_on_hand);
    }

    public function vatPercent(?float $shopDefault = null): float
    {
        if ($this->vat_rate !== null) {
            return (float) $this->vat_rate;
        }

        return $shopDefault ?? 5.0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('qty_on_hand', '<=', 'min_qty');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $normalized = Sku::normalize($term);

        return $query->where(function (Builder $inner) use ($term, $normalized): void {
            $inner->where('name', 'like', '%'.$term.'%')
                ->orWhere('sku', 'like', '%'.$term.'%')
                ->orWhere('sku_normalized', 'like', '%'.$normalized.'%');
        });
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
