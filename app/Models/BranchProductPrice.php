<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchProductPrice extends Model
{
    protected $fillable = [
        'branch_id',
        'product_id',
        'last_cost_fils',
        'profit_percent',
        'floor_fils',
    ];

    protected function casts(): array
    {
        return [
            'last_cost_fils' => 'integer',
            'profit_percent' => 'decimal:2',
            'floor_fils' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recalculateFloor(): void
    {
        $this->floor_fils = (int) round($this->last_cost_fils * (100 + (float) $this->profit_percent) / 100);
        if ($this->floor_fils < 0) {
            $this->floor_fils = 0;
        }
    }

    public static function forProduct(int $branchId, int $productId): self
    {
        $row = static::query()->firstOrNew([
            'branch_id' => $branchId,
            'product_id' => $productId,
        ]);
        if (! $row->exists) {
            $row->last_cost_fils = 0;
            $row->profit_percent = 0;
            $row->floor_fils = 0;
        }

        return $row;
    }
}
