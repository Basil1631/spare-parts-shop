@php $g = $garage ?? null; @endphp
<x-layouts.app :title="$g ? 'Edit garage' : 'Add garage'">
    <h1 class="text-2xl font-semibold mb-4">{{ $g ? 'Edit garage' : 'Add garage' }}</h1>
    <form method="POST" action="{{ $g ? route('garages.update', $g) : route('garages.store') }}" class="bg-white rounded-lg shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @if ($g) @method('PUT') @endif
        <div>
            <label class="block text-sm font-medium mb-1">Garage name</label>
            <input name="name" required value="{{ old('name', $g?->name ?? '') }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input name="phone" value="{{ old('phone', $g?->phone ?? '') }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" class="w-full border rounded-md px-3 py-2">{{ old('address', $g?->address ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">TRN (if VAT registered)</label>
            <input name="trn" value="{{ old('trn', $g?->trn ?? '') }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Default payment type</label>
            <select name="payment_type" class="w-full border rounded-md px-3 py-2">
                @foreach (\App\Enums\PaymentType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('payment_type', $g?->payment_type?->value ?? 'cash') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">Used to fill billing. Staff can still change payment on each bill.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Credit days (for credit default)</label>
            <input name="credit_days" type="number" min="0" value="{{ old('credit_days', $g?->credit_days ?? 30) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Installment count (for installment default)</label>
            <input name="installment_count" type="number" min="1" value="{{ old('installment_count', $g?->installment_count ?? 3) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <button class="bg-slate-900 text-white px-4 py-2 rounded-md">Save</button>
    </form>
</x-layouts.app>
