<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Enums\InstallmentStatus;
use App\Enums\PaymentType;
use App\Models\Bill;
use App\Models\Installment;
use App\Services\CollectionService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(): View
    {
        $installments = Installment::query()
            ->open()
            ->with('bill.garage')
            ->whereHas('bill', fn ($q) => $q->where('status', BillStatus::Issued->value))
            ->orderBy('due_date')
            ->paginate(30, ['*'], 'installments');

        $credits = Bill::query()
            ->issued()
            ->where('payment_type', PaymentType::Credit->value)
            ->whereRaw('(total_fils - paid_fils - credited_fils) > 0')
            ->orderBy('credit_due_date')
            ->paginate(30, ['*'], 'credits');

        return view('collections.index', compact('installments', 'credits'));
    }

    public function store(Request $request, CollectionService $collections): RedirectResponse
    {
        $data = $request->validate([
            'bill_id' => ['required', 'exists:bills,id'],
            'installment_id' => ['nullable', 'exists:installments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,bank,other'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $bill = Bill::query()->findOrFail($data['bill_id']);
        $collections->record(
            $bill,
            Money::toFils($data['amount']),
            $request->user(),
            isset($data['installment_id']) ? (int) $data['installment_id'] : null,
            $data['method'],
            $data['note'] ?? null,
        );

        return back()->with('status', 'Payment recorded.');
    }
}
