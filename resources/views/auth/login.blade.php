<x-layouts.app title="Sign in · Partzeno">
    <div class="bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)] rounded-[28px] px-8 py-9 border border-white">
        <h2 class="text-[22px] font-semibold text-slate-900">Sign in</h2>
        <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label class="block text-sm text-slate-600 mb-1.5">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                       class="w-full bg-white border border-slate-200 rounded-full px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
            </div>
            <div>
                <label class="block text-sm text-slate-600 mb-1.5">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full bg-white border border-slate-200 rounded-full px-4 py-2.5 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-700" aria-label="Show password">
                        <svg id="eye-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z"/>
                        </svg>
                        <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.93 12S5.68 18.75 12 18.75c1.69 0 3.26-.35 4.64-.97M9.88 9.88A3 3 0 0 1 14.12 14.12M6.23 6.23 17.77 17.77M9.88 9.88 6.23 6.23m7.89 7.89 3.65 3.65"/>
                        </svg>
                    </button>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-500">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> Remember me
            </label>
            <button class="w-full bg-[#0a1628] hover:bg-[#111d33] text-white rounded-full py-2.5 text-sm font-medium">Continue</button>
        </form>
    </div>
    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const open = document.getElementById('eye-open');
            const closed = document.getElementById('eye-closed');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            open.classList.toggle('hidden', show);
            closed.classList.toggle('hidden', !show);
            this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    </script>
</x-layouts.app>
