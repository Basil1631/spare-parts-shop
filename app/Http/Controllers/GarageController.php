<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GarageController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim($request->string('q')->toString());
        $garages = Garage::query()
            ->when($q !== '', fn ($query) => $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', '%'.$q.'%')->orWhere('phone', 'like', '%'.$q.'%');
            }))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('garages.index', compact('garages'));
    }

    public function create(): View
    {
        return view('garages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Garage::query()->create($this->validated($request));

        return redirect()->route('garages.index')->with('status', 'Garage added.');
    }

    public function show(Garage $garage): View
    {
        $garage->load(['bills' => fn ($q) => $q->latest('billed_at')->limit(50), 'payments' => fn ($q) => $q->latest('paid_at')->limit(50)]);

        return view('garages.show', compact('garage'));
    }

    public function edit(Garage $garage): View
    {
        return view('garages.edit', compact('garage'));
    }

    public function update(Request $request, Garage $garage): RedirectResponse
    {
        $garage->update($this->validated($request));

        return redirect()->route('garages.show', $garage)->with('status', 'Garage updated. New bills will use the new payment default.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'trn' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['required', Rule::enum(PaymentType::class)],
            'credit_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'installment_count' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        if ($data['payment_type'] !== PaymentType::Credit->value) {
            $data['credit_days'] = $data['credit_days'] ?: null;
        }
        if ($data['payment_type'] !== PaymentType::Installment->value) {
            $data['installment_count'] = $data['installment_count'] ?: null;
        }

        return $data;
    }
}
