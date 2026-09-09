<div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium mb-1">Garage (optional)</label>
                <select wire:model.live="garage_id" class="w-full border rounded-md px-3 py-2">
                    <option value="">Walk-in / ready cash</option>
                    @foreach ($garages as $garage)
                        <option value="{{ $garage->id }}">{{ $garage->name }} — {{ $garage->payment_type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium mb-1">Add product (name or SKU)</label>
                <input wire:model.live.debounce.200ms="search" class="w-full border rounded-md px-3 py-2" placeholder="Type to search…" autofocus>
                @if ($matches->isNotEmpty())
                    <ul class="border rounded-md mt-2 divide-y">
                        @foreach ($matches as $product)
                            <li>
                                <button type="button" wire:click="addProduct({{ $product->id }})" class="w-full text-left px-3 py-2 hover:bg-slate-50">
                                    <span class="font-medium">{{ $product->name }}</span>
                                    <span class="text-slate-500 text-sm">{{ $product->sku }} · stock {{ $product->qty_on_hand }} · AED {{ \App\Support\Money::fromFils($product->price_fils) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-3 py-2">Item</th>
                            <th>Qty</th>
                            <th>Rate AED</th>
                            <th>VAT %</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($lines as $i => $line)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $line['name'] }}<div class="text-xs text-slate-500">{{ $line['sku'] }} · on hand {{ $line['stock'] }}</div></td>
                            <td><input type="number" min="1" wire:model.blur="lines.{{ $i }}.qty" class="border rounded w-20 px-2 py-1"></td>
                            <td><input wire:model.blur="lines.{{ $i }}.unit_price" class="border rounded w-28 px-2 py-1"></td>
                            <td>{{ $line['vat_rate'] }}</td>
                            <td><button type="button" wire:click="removeLine({{ $i }})" class="text-red-700">Remove</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-slate-500">Search and add spare parts to this bill.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-4 space-y-3">
                <h2 class="font-semibold">Payment</h2>
                <p class="text-xs text-slate-500">Filled from garage default. Recorrect if this sale is different.</p>
                <select wire:model.live="payment_type" class="w-full border rounded-md px-3 py-2" @if(! $garage_id) disabled @endif>
                    @foreach (\App\Enums\PaymentType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                @if (! $garage_id)
                    <input type="hidden" wire:model="payment_type">
                    <p class="text-xs text-amber-700">Walk-in is ready cash only.</p>
                @endif
                @if ($payment_type === 'credit')
                    <div>
                        <label class="text-sm">Payment due date</label>
                        <input type="date" wire:model="credit_due_date" class="w-full border rounded-md px-3 py-2">
                    </div>
                @endif
                @if ($payment_type === 'installment')
                    <div>
                        <label class="text-sm">Number of installments</label>
                        <input type="number" min="1" max="24" wire:model="installment_count" class="w-full border rounded-md px-3 py-2">
                        <p class="text-xs text-slate-500 mt-1">Each installment due on the shop collection day (default 3rd of the month).</p>
                    </div>
                @endif
                <textarea wire:model="notes" placeholder="Notes" class="w-full border rounded-md px-3 py-2"></textarea>
            </div>
            <div class="bg-slate-900 text-white rounded-lg p-4 space-y-1">
                <div class="flex justify-between text-sm"><span>Taxable</span><span>AED {{ \App\Support\Money::fromFils($totals['subtotal']) }}</span></div>
                <div class="flex justify-between text-sm"><span>VAT</span><span>AED {{ \App\Support\Money::fromFils($totals['vat']) }}</span></div>
                <div class="flex justify-between font-semibold text-lg pt-2"><span>Total</span><span>AED {{ \App\Support\Money::fromFils($totals['total']) }}</span></div>
                <button wire:click="confirm" class="mt-3 w-full bg-amber-400 text-slate-900 font-bold py-2 rounded-md">Issue tax invoice</button>
            </div>
        </div>
    </div>
</div>
