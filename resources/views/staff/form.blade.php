@php $m = $member ?? null; @endphp
<x-layouts.app :title="$m ? 'Edit staff' : 'Add staff'">
    <h1 class="text-2xl font-semibold mb-4">{{ $m ? 'Edit staff' : 'Add staff' }}</h1>
    <form method="POST" action="{{ $m ? route('staff.update', $m) : route('staff.store') }}" class="bg-white p-6 rounded-lg max-w-xl space-y-3">
        @csrf
        @if ($m) @method('PUT') @endif
        <input name="name" required value="{{ old('name', $m?->name) }}" placeholder="Name" class="w-full border rounded px-3 py-2">
        <input name="email" type="email" required value="{{ old('email', $m?->email) }}" placeholder="Email (login)" class="w-full border rounded px-3 py-2">
        <select name="role" class="w-full border rounded px-3 py-2">
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $m?->getRoleNames()->first()) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
        @if (auth()->user()->isAdmin())
            <select name="branch_id" required class="w-full border rounded px-3 py-2">
                <option value="">Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id', $m?->branch_id) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        @endif
        <label class="text-sm">Monthly salary (AED)</label>
        <input name="monthly_salary" type="number" step="0.01" min="0" required value="{{ old('monthly_salary', $m ? number_format($m->monthly_salary_fils/100, 2, '.', '') : '0') }}" class="w-full border rounded px-3 py-2">
        <label class="text-sm">Incentive % of extra sales above target</label>
        <input name="incentive_percent" type="number" step="0.1" min="0" required value="{{ old('incentive_percent', $m?->incentive_percent ?? 5) }}" class="w-full border rounded px-3 py-2">
        <input name="password" type="password" placeholder="{{ $m ? 'Leave blank to keep password' : 'Password' }}" class="w-full border rounded px-3 py-2" @if(!$m) required @endif>
        <input name="password_confirmation" type="password" placeholder="Confirm password" class="w-full border rounded px-3 py-2">
        @if ($m)
            <label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m->is_active))> Active</label>
        @endif
        <button class="bg-slate-900 text-white px-4 py-2 rounded">Save</button>
    </form>
</x-layouts.app>
