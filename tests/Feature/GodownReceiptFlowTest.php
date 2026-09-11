<?php

namespace Tests\Feature;

use App\Enums\PurchaseStatus;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GodownReceiptFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_raise_does_not_change_stock_confirm_adds_actual_qty_then_accountant_pays(): void
    {
        $this->seed(DatabaseSeeder::class);

        $product = Product::query()->create([
            'name' => 'Brake Pad',
            'sku' => 'BP-900',
            'price_fils' => 20000,
            'min_qty' => 0,
            'qty_on_hand' => 5,
            'active' => true,
        ]);

        $purchaseUser = User::query()->where('email', 'purchase@shop.local')->firstOrFail();
        $this->actingAs($purchaseUser)
            ->post('/purchases', [
                'supplier_name' => 'SECRET-SUPPLIER',
                'invoice_number' => 'INV-HIDE-99',
                'purchased_on' => now()->toDateString(),
                'product_id' => [$product->id],
                'qty' => [10],
                'unit_cost' => ['12.50'],
            ])
            ->assertRedirect();

        $this->assertSame(5, $product->fresh()->qty_on_hand);

        $purchase = Purchase::query()->latest('id')->firstOrFail();
        $this->assertSame(PurchaseStatus::AwaitingGodown, $purchase->status);

        $this->actingAs($purchaseUser)->get('/godown')->assertForbidden();
        $this->actingAs($purchaseUser)
            ->post('/godown/'.$purchase->id, ['received' => [$purchase->items->first()->id => 3]])
            ->assertForbidden();

        $godown = User::query()->where('email', 'godown@shop.local')->firstOrFail();
        $this->actingAs($godown)->get('/godown')->assertOk()->assertDontSee('SECRET-SUPPLIER');
        $this->actingAs($godown)->get('/purchases/'.$purchase->id)->assertForbidden();
        $this->actingAs($godown)->get('/vendor-bills')->assertForbidden();
        $this->actingAs($godown)->get('/godown/'.$purchase->id)
            ->assertOk()
            ->assertSee('Brake Pad')
            ->assertSee('BP-900')
            ->assertDontSee('SECRET-SUPPLIER')
            ->assertDontSee('INV-HIDE-99')
            ->assertDontSee('12.50');

        $itemId = $purchase->items->first()->id;
        $this->actingAs($godown)
            ->post('/godown/'.$purchase->id, ['received' => [$itemId => 3]])
            ->assertRedirect('/godown');

        $this->assertSame(8, $product->fresh()->qty_on_hand);
        $purchase->refresh();
        $this->assertSame(PurchaseStatus::Received, $purchase->status);
        $this->assertSame(3, $purchase->items->first()->received_qty);

        $accountant = User::query()->where('email', 'accounts@shop.local')->firstOrFail();
        $this->actingAs($accountant)->get('/godown')->assertForbidden();
        $this->actingAs($accountant)
            ->post('/godown/'.$purchase->id, ['received' => [$itemId => 1]])
            ->assertForbidden();
        $this->actingAs($accountant)->get('/vendor-bills')->assertOk()->assertSee('SECRET-SUPPLIER');
        $this->actingAs($accountant)->get('/vendor-bills/'.$purchase->id)
            ->assertOk()
            ->assertSee('INV-HIDE-99')
            ->assertSee('12.50');

        $this->actingAs($accountant)
            ->post('/vendor-bills/'.$purchase->id.'/pay')
            ->assertRedirect();
        $this->assertSame(PurchaseStatus::Paid, $purchase->fresh()->status);

        $this->actingAs($accountant)
            ->post('/vendor-bills/'.$purchase->id.'/unpay')
            ->assertRedirect();
        $this->assertSame(PurchaseStatus::Received, $purchase->fresh()->status);
    }
}
