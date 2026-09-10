<?php

namespace App\Livewire;

use App\Enums\PaymentType;
use App\Models\Garage;
use App\Models\Product;
use App\Models\ShopSetting;
use App\Services\BillingService;
use App\Support\Money;
use Illuminate\Support\Collection;
use Livewire\Component;
use RuntimeException;

class CreateBill extends Component
{
    public mixed $garage_id = null;

    public string $payment_type = 'cash';

    public ?string $credit_due_date = null;

    public int $installment_count = 2;

    public string $search = '';

    public string $notes = '';

    public string $customer_kind = 'regular';

    public float $markup_percent = 5;

    /** @var list<array{product_id:int,name:string,sku:string,qty:int,unit_price:string,vat_rate:float,stock:int,floor_fils:int}> */
    public array $lines = [];

    public function mount(): void
    {
        $this->credit_due_date = now()->addDays(30)->toDateString();
        $this->markup_percent = 5;
    }

    public function updatedCustomerKind($value): void
    {
        if ($value === 'manual') {
            return;
        }
        $this->markup_percent = match ($value) {
            'regular' => 5,
            'new' => 20,
            default => (float) $this->markup_percent,
        };
        $this->applyMarkup();
    }

    public function setPricing(string $kind): void
    {
        $this->customer_kind = $kind;
        $this->updatedCustomerKind($kind);
    }

    public function updatedLines($value, $key): void
    {
        if (is_string($key) && str_contains($key, 'unit_price')) {
            $this->customer_kind = 'manual';
        }
    }

    public function updatedMarkupPercent(): void
    {
        $this->customer_kind = 'custom';
        $this->applyMarkup();
    }

    public function applyMarkup(): void
    {
        foreach ($this->lines as $i => $line) {
            $floor = (int) ($line['floor_fils'] ?? 0);
            $sell = (int) round($floor * (100 + (float) $this->markup_percent) / 100);
            $this->lines[$i]['unit_price'] = number_format(max($floor, $sell) / 100, 2, '.', '');
        }
    }

    public function updatedGarageId($value): void
    {
        if (! $value) {
            $this->payment_type = PaymentType::Cash->value;

            return;
        }

        $garage = Garage::query()->find($value);
        if (! $garage) {
            return;
        }

        $this->payment_type = $garage->payment_type->value;
        if ($garage->credit_days) {
            $this->credit_due_date = now()->addDays($garage->credit_days)->toDateString();
        }
        if ($garage->installment_count) {
            $this->installment_count = $garage->installment_count;
        }
    }

    public function addProduct(int $productId): void
    {
        $product = Product::query()->active()->find($productId);
        if (! $product) {
            return;
        }

        foreach ($this->lines as $i => $line) {
            if ($line['product_id'] === $product->id) {
                $this->lines[$i]['qty']++;
                $this->search = '';

                return;
            }
        }

        $floor = $product->floorFilsFor(auth()->user()?->branch_id);
        $sell = $this->customer_kind === 'manual'
            ? $floor
            : (int) round($floor * (100 + (float) $this->markup_percent) / 100);

        $this->lines[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'qty' => 1,
            'unit_price' => number_format(max($floor, $sell) / 100, 2, '.', ''),
            'vat_rate' => $product->vatPercent((float) ShopSetting::current()->vat_percent),
            'stock' => $product->qty_on_hand,
            'floor_fils' => $floor,
        ];
        $this->search = '';
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function confirm(BillingService $billing)
    {
        if ($this->lines === []) {
            $this->addError('lines', 'Add at least one product.');

            return;
        }

        try {
            $payload = [
                'garage_id' => $this->garage_id ?: null,
                'payment_type' => $this->payment_type,
                'credit_due_date' => $this->credit_due_date,
                'installment_count' => $this->installment_count,
                'notes' => $this->notes,
                'markup_percent' => $this->markup_percent,
                'customer_kind' => $this->customer_kind,
            ];

            $lines = [];
            foreach ($this->lines as $line) {
                $lines[] = [
                    'product_id' => $line['product_id'],
                    'qty' => (int) $line['qty'],
                    'unit_price_fils' => Money::toFils($line['unit_price']),
                ];
            }

            $bill = $billing->create($payload, $lines, auth()->user());

            return redirect()->route('bills.show', $bill)->with('status', 'Tax invoice '.$bill->number.' issued. Stock updated.');
        } catch (RuntimeException $e) {
            $this->addError('lines', $e->getMessage());
        }
    }

    public function totals(): array
    {
        $subtotal = 0;
        $vat = 0;
        foreach ($this->lines as $line) {
            $qty = max(0, (int) $line['qty']);
            $unit = Money::toFils($line['unit_price']);
            $lineSub = $unit * $qty;
            $lineVat = Money::vatFils($lineSub, $line['vat_rate']);
            $subtotal += $lineSub;
            $vat += $lineVat;
        }

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $subtotal + $vat,
        ];
    }

    public function matches(): Collection
    {
        $term = trim($this->search);
        if (strlen($term) < 1) {
            return collect();
        }

        return Product::query()->active()->search($term)->orderBy('name')->limit(8)->get();
    }

    public function render()
    {
        return view('livewire.create-bill', [
            'garages' => Garage::query()->orderBy('name')->get(),
            'matches' => $this->matches(),
            'totals' => $this->totals(),
        ]);
    }
}
