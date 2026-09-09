<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Spare Parts Shop' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#f0f7ff', 600:'#1d4ed8', 700:'#1e40af', 900:'#0f172a' }
                    }
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    @auth
        <header class="bg-slate-900 text-white">
            <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center gap-3">
                <a href="{{ route('bills.create') }}" class="bg-amber-400 text-slate-900 font-bold px-4 py-2 rounded-md shadow hover:bg-amber-300">Bill</a>
                <a href="{{ route('dashboard') }}" class="hover:text-amber-300 {{ request()->routeIs('dashboard') ? 'text-amber-300' : '' }}">Dashboard</a>
                <a href="{{ route('products.index') }}" class="hover:text-amber-300 {{ request()->routeIs('products.*') ? 'text-amber-300' : '' }}">Products</a>
                <a href="{{ route('stock.index') }}" class="hover:text-amber-300 {{ request()->routeIs('stock.*') ? 'text-amber-300' : '' }}">Stock</a>
                <a href="{{ route('garages.index') }}" class="hover:text-amber-300 {{ request()->routeIs('garages.*') ? 'text-amber-300' : '' }}">Garages</a>
                <a href="{{ route('collections.index') }}" class="hover:text-amber-300 {{ request()->routeIs('collections.*') ? 'text-amber-300' : '' }}">Collections</a>
                <a href="{{ route('bills.index') }}" class="hover:text-amber-300 {{ request()->routeIs('bills.index') || request()->routeIs('bills.show') ? 'text-amber-300' : '' }}">Bills</a>
                @role('admin')
                    <a href="{{ route('settings.edit') }}" class="hover:text-amber-300">Settings</a>
                @endrole
                <div class="ml-auto flex items-center gap-3 text-sm">
                    <span class="text-slate-300">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-slate-300 hover:text-white">Logout</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main class="max-w-7xl mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
