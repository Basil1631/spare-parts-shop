<x-layouts.app title="Login">
    <div class="max-w-md mx-auto mt-16 bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-semibold mb-1">Spare Parts Shop</h1>
        <p class="text-slate-500 mb-6">Sign in to billing and stock.</p>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required class="w-full border rounded-md px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input name="password" type="password" required class="w-full border rounded-md px-3 py-2">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="w-full bg-slate-900 text-white rounded-md py-2 font-medium">Sign in</button>
        </form>
        <p class="mt-4 text-xs text-slate-500">Default admin: admin@shop.local / password</p>
    </div>
</x-layouts.app>
