<?php

namespace Tests\Feature;

use App\Enums\PaymentType;
use App\Models\Garage;
use App\Models\Product;
use App\Models\User;
use App\Services\BillingService;
use App\Services\CollectionService;
use App\Services\CreditNoteService;
use App\Services\StockService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_reduces_stock_installments_collection_and_credit_note(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@shop.local')->firstOrFail();

        $product = Product::query()->create([
            'name' => 'Oil Filter',
            'sku' => 'OF-100',
            'price_fils' => 10000,
            'min_qty' => 10,
            'qty_on_hand' => 0,
            'active' => true,
        ]);

        app(StockService::class)->receive($product, 8, $admin, 'Opening');
        $product->refresh();
        $this->assertTrue($product->isLowStock());
        $this->assertSame(2, $product->suggestedOrderQty());

        $garage = Garage::query()->create([
            'name' => 'Al Ain Garage',
            'payment_type' => PaymentType::Installment,
            'installment_count' => 3,
            'trn' => '100000000000003',
        ]);

        $bill = app(BillingService::class)->create(
            [
                'garage_id' => $garage->id,
                'payment_type' => PaymentType::Installment->value,
                'installment_count' => 3,
            ],
            [['product_id' => $product->id, 'qty' => 2]],
            $admin,
        );

        $this->assertSame(6, $product->fresh()->qty_on_hand);
        $this->assertCount(3, $bill->installments);
        $this->assertSame(21000, $bill->total_fils);
        $this->assertTrue(str_starts_with($bill->number, 'INV-'));

        $first = $bill->installments->first();
        app(CollectionService::class)->record($bill, $first->amount_fils, $admin, $first->id);
        $this->assertSame($first->amount_fils, $bill->fresh()->paid_fils);

        $note = app(CreditNoteService::class)->create(
            $bill->fresh(),
            [$bill->items->first()->id => 1],
            'Goods returned',
            $admin,
        );

        $this->assertSame(7, $product->fresh()->qty_on_hand);
        $this->assertTrue(str_starts_with($note->number, 'CN-'));
        $this->assertGreaterThan(0, $bill->fresh()->credited_fils);
    }

    public function test_same_day_void_restores_stock(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@shop.local')->firstOrFail();

        $product = Product::query()->create([
            'name' => 'Pad',
            'sku' => 'PAD-1',
            'price_fils' => 5000,
            'min_qty' => 1,
            'qty_on_hand' => 0,
            'active' => true,
        ]);
        app(StockService::class)->receive($product, 5, $admin);

        $bill = app(BillingService::class)->create(
            ['payment_type' => PaymentType::Cash->value],
            [['product_id' => $product->id, 'qty' => 1]],
            $admin,
        );

        $this->assertSame(4, $product->fresh()->qty_on_hand);
        app(BillingService::class)->void($bill, $admin, 'Wrong bill');
        $this->assertSame(5, $product->fresh()->qty_on_hand);
        $this->assertFalse($bill->fresh()->isIssued());
    }

    public function test_cannot_oversell(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@shop.local')->firstOrFail();
        $product = Product::query()->create([
            'name' => 'Belt',
            'sku' => 'B-1',
            'price_fils' => 1000,
            'min_qty' => 0,
            'qty_on_hand' => 0,
            'active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        app(BillingService::class)->create(
            ['payment_type' => PaymentType::Cash->value],
            [['product_id' => $product->id, 'qty' => 1]],
            $admin,
        );
    }
}
