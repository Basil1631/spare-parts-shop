<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $bills = Bill::query()
            ->with('garage')
            ->search($request->string('q')->toString())
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('billed_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('billed_at', '<=', $to))
            ->latest('billed_at')
            ->paginate(25)
            ->withQueryString();

        return view('bills.index', compact('bills'));
    }

    public function create(): View
    {
        return view('bills.create');
    }

    public function show(Bill $bill): View
    {
        $bill->load(['items', 'installments', 'payments', 'creditNotes', 'garage', 'creator']);

        return view('bills.show', compact('bill'));
    }

    public function pdf(Bill $bill): Response
    {
        $bill->load(['items', 'garage']);
        $pdf = Pdf::loadView('bills.tax-invoice', ['bill' => $bill, 'shop' => \App\Models\ShopSetting::current()]);

        return $pdf->download($bill->number.'.pdf');
    }

    public function print(Bill $bill): View
    {
        $bill->load(['items', 'garage']);

        return view('bills.tax-invoice', [
            'bill' => $bill,
            'shop' => \App\Models\ShopSetting::current(),
            'print' => true,
        ]);
    }

    public function void(Request $request, Bill $bill, BillingService $billing): RedirectResponse
    {
        $this->authorize('void-bill');

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $billing->void($bill, $request->user(), $data['reason'] ?? null);

        return redirect()->route('bills.show', $bill)->with('status', 'Bill voided. Stock restored.');
    }
}
