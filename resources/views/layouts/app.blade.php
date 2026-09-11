<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Partzeno' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Inter:wght@400;500;600&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DM Sans', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        ink: { 900: '#0b1220', 800: '#111827', 700: '#1e293b' },
                        brand: { 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488' }
                    }
                }
            }
        }
    </script>
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .nav-scroll::-webkit-scrollbar { width: 6px; }
        .nav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 99px; }
    </style>
</head>
<body class="min-h-screen bg-[#f3f5f8] text-slate-800 font-sans antialiased">
@php
    $u = auth()->user();
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V21a.75.75 0 0 1-.75.75h-5.5V14h-5.5v7.75H3.75A.75.75 0 0 1 3 21V10.5Z"/>',
        'bill' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 3.75h8A2.25 2.25 0 0 1 18.25 6v14.25L12 17.25l-6.25 3V6A2.25 2.25 0 0 1 8 3.75Z"/>',
        'list' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6.75h12M6 12h12M6 17.25h8"/>',
        'garage' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5V9.75L12 3.75l8.25 6V19.5h-5.25v-6h-6v6H3.75Z"/>',
        'cash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5v9H3.75v-9Zm4.5 4.5h1.5m5.25 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>',
        'box' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5 12 3 3 7.5m18 0-9 4.5m9-4.5v9l-9 4.5m0-9L3 7.5m9 4.5v9m0-9L3 16.5v-9"/>',
        'stock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
        'truck' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25V15H3V7.5Zm11.25 3h4.2L21 13.2V15h-6.75v-4.5ZM6 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10.5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5V5.25A1.75 1.75 0 0 1 6.25 3.5h5.25L20 12l-7.75 7.75L4.5 10.5Zm4.125-4.125a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V9.75M10.5 19.5V4.5m6 15V12m3.75 7.5H3.75"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0"/>',
        'target' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0-9-9m9 4.5a4.5 4.5 0 1 0-4.5-4.5M12 12H3"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75V12l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        'salary' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3.75-9.75A3.75 3.75 0 0 0 12 6 3.75 3.75 0 0 0 8.25 9.75c0 2.25 7.5 2.25 7.5 4.5A3.75 3.75 0 0 1 12 18a3.75 3.75 0 0 1-3.75-3.75"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25V6.75L12 3.75l7.5 3v13.5M9 20.25V12h6v8.25"/>',
        'cog' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.3 3.9 9.8 6.2a7.6 7.6 0 0 0-1.7 1L5.9 6.6 3.8 8.7l1.6 2.2a7.6 7.6 0 0 0 0 2.2L3.8 15.3l2.1 2.1 2.2-1.6a7.6 7.6 0 0 0 1.7 1l.5 2.3h3l.5-2.3a7.6 7.6 0 0 0 1.7-1l2.2 1.6 2.1-2.1-1.6-2.2a7.6 7.6 0 0 0 0-2.2l1.6-2.2-2.1-2.1-2.2 1.6a7.6 7.6 0 0 0-1.7-1L13.7 3.9h-3.4ZM12 14.25A2.25 2.25 0 1 1 12 9.75a2.25 2.25 0 0 1 0 4.5Z"/>',
    ];
    $nav = function (string $route, string $label, string $icon, $active = null) use ($icons) {
        $is = is_array($active) ? request()->routeIs(...$active) : request()->routeIs($active ?: $route);
        $cls = $is
            ? 'bg-white/10 text-white shadow-inner ring-1 ring-white/10'
            : 'text-slate-300 hover:bg-white/5 hover:text-white';
        $svg = $icons[$icon] ?? $icons['list'];
        echo '<a href="'.e(route($route)).'" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition '.$cls.'">';
        echo '<svg class="w-[18px] h-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">'.$svg.'</svg>';
        echo e($label).'</a>';
    };
@endphp

@guest
    <div class="min-h-screen grid lg:grid-cols-2" style="font-family: Inter, ui-sans-serif, system-ui, sans-serif;">
        <div class="hidden lg:flex flex-col min-h-screen bg-[#09131F] px-12 py-12">
            <div class="flex-1"></div>
            <div class="flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 140" class="w-[min(420px,72%)] h-auto" role="img" aria-label="Partzeno">
                    <defs>
                        <linearGradient id="partzenoWordmarkDesktop" x1="70" y1="28" x2="450" y2="88" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#D7F08A"/>
                            <stop offset="0.45" stop-color="#B4E068"/>
                            <stop offset="1" stop-color="#86C94A"/>
                        </linearGradient>
                    </defs>
                    <text x="260" y="72" text-anchor="middle" fill="url(#partzenoWordmarkDesktop)" font-family="Poppins, Inter, sans-serif" font-size="64" font-weight="600">Partzeno</text>
                    <text x="260" y="108" text-anchor="middle" font-family="Inter, sans-serif" font-size="16">
                        <tspan fill="#8B7CF6">Powered By </tspan>
                        <tspan fill="#9AA3B0" font-weight="500">Inktek Solutions</tspan>
                    </text>
                </svg>
            </div>
            <p class="flex-1 flex items-end justify-center text-[13px] text-[#8B93A0] text-center">Copyright © 2026 Inktek Solutions. All rights reserved.</p>
        </div>
        <div class="flex flex-col items-center justify-center min-h-screen px-6 py-10 bg-[#F4F5F9]">
            <div class="lg:hidden w-full max-w-[400px] mb-8 rounded-[24px] bg-[#09131F] px-6 py-10 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 140" class="w-full h-auto" role="img" aria-label="Partzeno">
                    <defs>
                        <linearGradient id="partzenoWordmarkMobile" x1="70" y1="28" x2="450" y2="88" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#D7F08A"/>
                            <stop offset="0.45" stop-color="#B4E068"/>
                            <stop offset="1" stop-color="#86C94A"/>
                        </linearGradient>
                    </defs>
                    <text x="260" y="72" text-anchor="middle" fill="url(#partzenoWordmarkMobile)" font-family="Poppins, Inter, sans-serif" font-size="64" font-weight="600">Partzeno</text>
                    <text x="260" y="108" text-anchor="middle" font-family="Inter, sans-serif" font-size="16">
                        <tspan fill="#8B7CF6">Powered By </tspan>
                        <tspan fill="#9AA3B0" font-weight="500">Inktek Solutions</tspan>
                    </text>
                </svg>
            </div>
            <div class="w-full max-w-[400px]">
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 px-4 py-3 text-sm">
                        <ul class="list-disc ml-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
@else
    <div class="min-h-screen lg:flex">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="toggleSidebar(false)"></div>
        <aside id="sidebar" class="fixed lg:sticky top-0 z-40 h-screen w-[272px] bg-ink-900 text-white flex flex-col shrink-0 -translate-x-full lg:translate-x-0 transition-transform">
            <div class="px-5 py-5 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-brand-500/20 text-brand-400 grid place-items-center font-bold">SP</div>
                    <div>
                        <div class="font-semibold leading-tight">Spare Parts</div>
                        <div class="text-xs text-slate-400">{{ $u->branch?->name ?? 'Head office' }}</div>
                    </div>
                </div>
            </div>
            <nav class="nav-scroll flex-1 overflow-y-auto px-3 py-4 space-y-5">
                <div>
                    <div class="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">Overview</div>
                    <div class="space-y-0.5">
                        <?php $nav('dashboard', 'Dashboard', 'home'); ?>
                    </div>
                </div>
                @if ($u->canBill() || $u->canCollect())
                    <div>
                        <div class="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">Sales</div>
                        <div class="space-y-0.5">
                            @if ($u->canBill())
                                <?php $nav('bills.create', 'New bill', 'bill'); ?>
                            @endif
                            @if ($u->canCollect())
                                <?php $nav('bills.index', 'Bills', 'list', ['bills.index', 'bills.show']); ?>
                                <?php $nav('garages.index', 'Garages', 'garage', 'garages.*'); ?>
                                <?php $nav('collections.index', 'Collections', 'cash'); ?>
                            @endif
                            @if ($u->canSeeVendorBills())
                                <?php $nav('vendor-bills.index', 'Vendor bills', 'truck', ['vendor-bills.index', 'vendor-bills.show']); ?>
                            @endif
                            @if ($u->canBill())
                                <?php $nav('reports.sales', 'Sales analysis', 'chart'); ?>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($u->canPurchase() || $u->canSeeStock() || $u->canConfirmGodown())
                    <div>
                        <div class="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">Stock</div>
                        <div class="space-y-0.5">
                            @if ($u->hasAnyRole(['admin', 'branch_manager', 'purchase']))
                                <?php $nav('products.index', 'Products', 'box', 'products.*'); ?>
                            @endif
                            @if ($u->canSeeStock())
                                <?php $nav('stock.index', 'Stock', 'stock'); ?>
                            @endif
                            @if ($u->canConfirmGodown())
                                <?php $nav('godown.index', 'Incoming stock', 'box', ['godown.index', 'godown.show']); ?>
                            @endif
                            @if ($u->canPurchase())
                                <?php $nav('purchases.index', 'Purchases', 'truck', 'purchases.*'); ?>
                            @endif
                            @if ($u->hasAnyRole(['admin', 'branch_manager']))
                                <?php $nav('pricing.index', 'Floor price', 'tag'); ?>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($u->canManageStaff() || $u->canSeeHr())
                    <div>
                        <div class="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">People</div>
                        <div class="space-y-0.5">
                            @if ($u->canManageStaff())
                                <?php $nav('staff.index', 'Staff', 'users', 'staff.*'); ?>
                                <?php $nav('targets.index', 'Targets', 'target'); ?>
                            @endif
                            @if ($u->canSeeHr())
                                <?php $nav('attendance.index', 'Attendance', 'clock'); ?>
                                <?php $nav('payroll.index', 'Salary & cuttings', 'salary'); ?>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($u->isAdmin())
                    <div>
                        <div class="px-3 mb-2 text-[11px] uppercase tracking-wider text-slate-500">Admin</div>
                        <div class="space-y-0.5">
                            <?php $nav('branches.index', 'Branches', 'building', 'branches.*'); ?>
                            <?php $nav('settings.edit', 'Settings', 'cog'); ?>
                        </div>
                    </div>
                @endif
            </nav>
            <div class="p-4 border-t border-white/10">
                <div class="text-sm font-medium truncate">{{ $u->name }}</div>
                <div class="text-xs text-slate-400 mb-3 truncate">{{ $u->getRoleNames()->join(' · ') }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left text-sm text-slate-300 hover:text-white px-3 py-2 rounded-xl hover:bg-white/5">Sign out</button>
                </form>
            </div>
        </aside>
        <div class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-slate-200/80">
                <div class="px-4 lg:px-8 h-16 flex items-center gap-3">
                    <button type="button" class="lg:hidden h-10 w-10 grid place-items-center rounded-xl border border-slate-200" onclick="toggleSidebar(true)" aria-label="Open menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold text-ink-800 leading-tight">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-slate-500 hidden sm:block">{{ now()->timezone(config('app.timezone'))->format('l, d M Y') }}</p>
                    </div>
                    @if ($u->canBill())
                        <a href="{{ route('bills.create') }}" class="ml-auto inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold px-4 py-2 rounded-xl shadow-sm">New bill</a>
                    @endif
                </div>
            </header>
            <main class="px-4 lg:px-8 py-6 max-w-7xl">
                @if (session('status'))
                    <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 px-4 py-3 text-sm">
                        <ul class="list-disc ml-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    <script>
        function toggleSidebar(open) {
            const side = document.getElementById('sidebar');
            const back = document.getElementById('sidebar-backdrop');
            if (open) { side.classList.remove('-translate-x-full'); back.classList.remove('hidden'); }
            else { side.classList.add('-translate-x-full'); back.classList.add('hidden'); }
        }
    </script>
@endguest
    @livewireScripts
</body>
</html>
