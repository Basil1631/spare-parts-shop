<x-layouts.app title="Dashboard">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <a href="{{ route('bills.create') }}" class="bg-amber-400 text-slate-900 font-bold px-4 py-2 rounded-md">New bill</a>
    </div>

    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="text-sm text-slate-500">Today's bills</div>
            <div class="text-2xl font-semibold">{{ $todayCount }}</div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="text-sm text-slate-500">Today sales (AED)</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($todayTotal) }}</div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="text-sm text-slate-500">Cash today</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($todayCash) }}</div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm {{ $lowStockCount ? 'ring-2 ring-red-500' : '' }}">
            <div class="text-sm text-slate-500">Low stock items</div>
            <div class="text-2xl font-semibold {{ $lowStockCount ? 'text-red-600' : '' }}">{{ $lowStockCount }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold text-red-700">Low quantity spare parts</h2>
                <a class="text-sm text-blue-700" href="{{ route('stock.index', ['filter' => 'low']) }}">View all</a>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-slate-500"><th class="py-1">Product</th><th>SKU</th><th>Qty</th><th>Min</th></tr></thead>
                <tbody>
                @forelse ($lowStock as $p)
                    <tr class="border-t bg-red-50">
                        <td class="py-2">{{ $p->name }}</td>
                        <td>{{ $p->sku }}</td>
                        <td class="font-semibold text-red-700">{{ $p->qty_on_hand }}</td>
                        <td>{{ $p->min_qty }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3 text-slate-500">All products are above minimum.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Due / overdue payments</h2>
                <a class="text-sm text-blue-700" href="{{ route('collections.index') }}">Collections</a>
            </div>
            <h3 class="text-xs uppercase text-slate-500 mb-1">Overdue installments</h3>
            <ul class="mb-3 space-y-1 text-sm">
                @forelse ($overdueInstallments as $row)
                    <li class="text-red-700">
                        {{ $row->bill->number }} · {{ $row->bill->garage_name }} · #{{ $row->sequence }} due {{ $row->due_date->format('d M Y') }} · AED {{ \App\Support\Money::fromFils($row->outstandingFils()) }}
                    </li>
                @empty
                    <li class="text-slate-500">None overdue.</li>
                @endforelse
            </ul>
            <h3 class="text-xs uppercase text-slate-500 mb-1">Credit bills</h3>
            <ul class="space-y-1 text-sm">
                @forelse ($dueCredit as $bill)
                    <li class="{{ $bill->credit_due_date?->isPast() ? 'text-red-700' : '' }}">
                        {{ $bill->number }} · {{ $bill->garage_name }} · due {{ $bill->credit_due_date?->format('d M Y') }} · AED {{ \App\Support\Money::fromFils($bill->outstandingFils()) }}
                    </li>
                @empty
                    <li class="text-slate-500">No open credit bills.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.app>
