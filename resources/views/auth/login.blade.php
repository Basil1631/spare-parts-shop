<x-layouts.app title="Sign in · Partzeno">
    <div class="bg-white rounded-[24px] px-8 pt-8 pb-8 shadow-[0_18px_50px_rgba(16,22,34,0.08)]">
        <h2 class="text-[20px] font-semibold text-[#101622] tracking-tight">Sign in</h2>
        <p class="mt-1 text-[13px] text-[#ABACB0] leading-snug">Same URL for every role. Use your own email and password.</p>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[13px] text-[#6B7280] mb-1.5">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                       class="w-full h-11 bg-white border border-[#E5E7EB] rounded-full px-4 text-[14px] text-[#101622] focus:outline-none focus:border-[#101622]">
            </div>
            <div>
                <label class="block text-[13px] text-[#6B7280] mb-1.5">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full h-11 bg-white border border-[#E5E7EB] rounded-full px-4 pr-11 text-[14px] text-[#101622] focus:outline-none focus:border-[#101622]">
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-3 flex items-center text-[#9CA3AF] hover:text-[#101622]" aria-label="Show password">
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
            <label class="flex items-center gap-2 text-[13px] text-[#6B7280]">
                <input type="checkbox" name="remember" class="rounded border-[#D1D5DB] text-[#101622] focus:ring-[#101622]"> Remember me
            </label>
            <button class="w-full h-11 bg-[#101622] text-white rounded-full text-[14px] font-medium">Continue</button>
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
