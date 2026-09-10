<x-layouts.app title="Dashboard">
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="text-sm text-slate-500">Today's bills</div>
            <div class="text-2xl font-semibold">{{ $todayCount }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="text-sm text-slate-500">Today sales (AED)</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($todayTotal) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="text-sm text-slate-500">This week vs last</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($weekSales) }}</div>
            <div class="text-xs text-slate-500">Last week {{ \App\Support\Money::fromFils($lastWeekSales) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="text-sm text-slate-500">This month vs last</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($monthSales) }}</div>
            <div class="text-xs text-slate-500">Last month {{ \App\Support\Money::fromFils($lastMonthSales) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <div class="text-sm text-slate-500">Cash today</div>
            <div class="text-2xl font-semibold">{{ \App\Support\Money::fromFils($todayCash) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 {{ $lowStockCount ? 'ring-2 ring-red-400' : '' }}">
            <div class="text-sm text-slate-500">Low stock items</div>
            <div class="text-2xl font-semibold {{ $lowStockCount ? 'text-red-600' : '' }}">{{ $lowStockCount }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h2 class="font-semibold mb-2">Present in shop today (login)</h2>
            <ul class="text-sm space-y-1">
                @forelse ($presentToday as $log)
                    <li>{{ $log->user?->name }} · {{ $log->user?->branch?->name }} · {{ $log->first_login_at->timezone(config('app.timezone'))->format('H:i') }}</li>
                @empty
                    <li class="text-slate-500">No logins yet today.</li>
                @endforelse
            </ul>
        </section>
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h2 class="font-semibold mb-2">High performing sales (this month)</h2>
            <ul class="text-sm space-y-1">
                @forelse ($topStaff as $row)
                    <li>{{ $row['user']?->name ?? '—' }} · AED {{ \App\Support\Money::fromFils($row['sales_fils']) }}</li>
                @empty
                    <li class="text-slate-500">No sales yet this month.</li>
                @endforelse
            </ul>
        </section>
    </div>

    @if ($branchProfits->isNotEmpty())
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
            <h2 class="font-semibold mb-2">Branch profit this month (sale − purchase cost)</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500"><tr><th class="py-1">Branch</th><th>Sales</th><th>Profit</th></tr></thead>
                <tbody>
                @foreach ($branchProfits as $row)
                    <tr class="border-t">
                        <td class="py-2">{{ $row['branch']->name }}</td>
                        <td>AED {{ \App\Support\Money::fromFils($row['sales']) }}</td>
                        <td>AED {{ \App\Support\Money::fromFils($row['profit']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
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

        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
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
