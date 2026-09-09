<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use RuntimeException;

class StockService
{
    public function receive(Product $product, int $qty, ?User $user = null, ?string $note = null): StockMovement
    {
        return $this->move($product, StockMovementType::Receive, $qty, $user, $note);
    }

    public function adjustOut(Product $product, int $qty, ?User $user = null, ?string $note = null): StockMovement
    {
        return $this->move($product, StockMovementType::AdjustOut, $qty, $user, $note);
    }

    public function move(
        Product $product,
        StockMovementType $type,
        int $qty,
        ?User $user = null,
        ?string $note = null,
        ?int $billId = null,
        ?int $creditNoteId = null,
        bool $lock = true,
    ): StockMovement {
        if ($qty <= 0) {
            throw new RuntimeException('Quantity must be greater than zero.');
        }

        $locked = $lock
            ? Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail()
            : $product;

        if (! $type->increasesStock() && $locked->qty_on_hand < $qty) {
            throw new RuntimeException("Not enough stock for {$locked->name} ({$locked->sku}). On hand: {$locked->qty_on_hand}.");
        }

        $locked->qty_on_hand += $type->increasesStock() ? $qty : -$qty;
        $locked->save();

        return StockMovement::query()->create([
            'product_id' => $locked->id,
            'type' => $type,
            'qty' => $qty,
            'bill_id' => $billId,
            'credit_note_id' => $creditNoteId,
            'user_id' => $user?->id,
            'note' => $note,
        ]);
    }
}
