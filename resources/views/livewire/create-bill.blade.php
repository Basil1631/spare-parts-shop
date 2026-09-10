<div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <label class="block text-sm font-medium mb-1">Garage (optional)</label>
                <select wire:model.live="garage_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5">
                    <option value="">Walk-in / ready cash</option>
                    @foreach ($garages as $garage)
                        <option value="{{ $garage->id }}">{{ $garage->name }} — {{ $garage->payment_type->label() }}</option>
                    @endforeach
                </select>

                <p class="text-sm font-medium mt-5 mb-2">Selling price</p>
                <div class="grid sm:grid-cols-3 gap-2">
                    <button type="button" wire:click="setPricing('regular')"
                        class="rounded-xl border px-3 py-3 text-left text-sm {{ $customer_kind === 'regular' ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="font-semibold">Regular garage</div>
                        <div class="text-slate-500 text-xs mt-0.5">+5% on floor · totals update</div>
                    </button>
                    <button type="button" wire:click="setPricing('new')"
                        class="rounded-xl border px-3 py-3 text-left text-sm {{ $customer_kind === 'new' ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="font-semibold">New customer</div>
                        <div class="text-slate-500 text-xs mt-0.5">+20% on floor · totals update</div>
                    </button>
                    <button type="button" wire:click="setPricing('manual')"
                        class="rounded-xl border px-3 py-3 text-left text-sm {{ $customer_kind === 'manual' ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-500' : 'border-slate-200 hover:border-slate-300' }}">
                        <div class="font-semibold">Manual price</div>
                        <div class="text-slate-500 text-xs mt-0.5">Type AED on each line</div>
                    </button>
                </div>
                @if ($customer_kind === 'manual')
                    <p class="text-xs text-teal-800 bg-teal-50 rounded-xl px-3 py-2 mt-3">Type the selling price in the Rate column. Totals on the right update as you type. Cannot go below floor.</p>
                @else
                    <p class="text-xs text-slate-500 mt-3">Preset applied to all lines. Switch to <span class="font-medium">Manual price</span> to type a different AED amount (still ≥ floor).</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <label class="block text-sm font-medium mb-1">Add product (name or SKU)</label>
                <input wire:model.live.debounce.200ms="search" class="w-full border border-slate-200 rounded-xl px-3 py-2.5" placeholder="Type to search…" autofocus>
                @if ($matches->isNotEmpty())
                    <ul class="border border-slate-200 rounded-xl mt-2 divide-y">
                        @foreach ($matches as $product)
                            <li>
                                <button type="button" wire:click="addProduct({{ $product->id }})" class="w-full text-left px-3 py-2.5 hover:bg-slate-50">
                                    <span class="font-medium">{{ $product->name }}</span>
                                    <span class="text-slate-500 text-sm">{{ $product->sku }} · stock {{ $product->qty_on_hand }} · floor AED {{ \App\Support\Money::fromFils($product->floorFilsFor(auth()->user()?->branch_id)) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-3 py-3">Item</th>
                            <th>Qty</th>
                            <th>Rate AED</th>
                            <th>Line</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($lines as $i => $line)
                        @php
                            $lineTotal = \App\Support\Money::toFils($line['unit_price']) * max(0, (int) $line['qty']);
                            $lineVat = \App\Support\Money::vatFils($lineTotal, $line['vat_rate']);
                        @endphp
                        <tr class="border-t">
                            <td class="px-3 py-3">{{ $line['name'] }}<div class="text-xs text-slate-500">{{ $line['sku'] }} · on hand {{ $line['stock'] }} · floor AED {{ \App\Support\Money::fromFils($line['floor_fils'] ?? 0) }} · VAT {{ $line['vat_rate'] }}%</div></td>
                            <td><input type="number" min="1" wire:model.live="lines.{{ $i }}.qty" class="border rounded-lg w-20 px-2 py-1.5"></td>
                            <td>
                                <input wire:model.live.debounce.120ms="lines.{{ $i }}.unit_price" class="border rounded-lg w-28 px-2 py-1.5 {{ $customer_kind === 'manual' ? 'ring-1 ring-teal-400' : '' }}">
                            </td>
                            <td class="whitespace-nowrap">AED {{ \App\Support\Money::fromFils($lineTotal + $lineVat) }}</td>
                            <td><button type="button" wire:click="removeLine({{ $i }})" class="text-red-600 text-sm">Remove</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-8 text-slate-500">Search and add spare parts to this bill.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                <h2 class="font-semibold">Payment</h2>
                <p class="text-xs text-slate-500">Filled from garage default. Change if this sale is different.</p>
                <select wire:model.live="payment_type" class="w-full border border-slate-200 rounded-xl px-3 py-2.5" @if(! $garage_id) disabled @endif>
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
                        <input type="date" wire:model="credit_due_date" class="w-full border rounded-xl px-3 py-2">
                    </div>
                @endif
                @if ($payment_type === 'installment')
                    <div>
                        <label class="text-sm">Number of installments</label>
                        <input type="number" min="1" max="24" wire:model="installment_count" class="w-full border rounded-xl px-3 py-2">
                        <p class="text-xs text-slate-500 mt-1">Each installment due on the shop collection day (default 3rd of the month).</p>
                    </div>
                @endif
                <textarea wire:model="notes" placeholder="Notes" class="w-full border border-slate-200 rounded-xl px-3 py-2"></textarea>
            </div>
            <div class="bg-ink-900 text-white rounded-2xl p-5 space-y-1" style="background:#0b1220">
                <div class="flex justify-between text-sm text-slate-300"><span>Taxable</span><span>AED {{ \App\Support\Money::fromFils($totals['subtotal']) }}</span></div>
                <div class="flex justify-between text-sm text-slate-300"><span>VAT</span><span>AED {{ \App\Support\Money::fromFils($totals['vat']) }}</span></div>
                <div class="flex justify-between font-semibold text-xl pt-2"><span>Total</span><span>AED {{ \App\Support\Money::fromFils($totals['total']) }}</span></div>
                <button wire:click="confirm" class="mt-4 w-full bg-teal-400 hover:bg-teal-300 text-slate-900 font-bold py-2.5 rounded-xl">Issue tax invoice</button>
            </div>
        </div>
    </div>
</div>
