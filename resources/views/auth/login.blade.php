<x-layouts.app title="Login">
<div class="w-full max-w-md">
    <div class="lg:hidden mb-8">
        <div class="text-teal-600 font-semibold text-sm tracking-wide">UAE COUNTER SYSTEM</div>
        <h1 class="text-2xl font-semibold mt-1">Spare Parts Shop</h1>
    </div>
    <div class="bg-white shadow-xl shadow-slate-200/60 rounded-3xl p-8 border border-slate-100">
        <h2 class="text-xl font-semibold text-slate-900">Sign in</h2>
        <p class="text-slate-500 text-sm mt-1 mb-6">Same URL for every role. Use your own email and password.</p>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input name="password" type="password" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded"> Remember me
            </label>
            <button class="w-full bg-slate-900 hover:bg-slate-800 text-white rounded-xl py-2.5 font-semibold">Continue</button>
        </form>
    </div>
</div>
</x-layouts.app>
