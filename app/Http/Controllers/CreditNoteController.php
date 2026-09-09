<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\ShopSetting;
use App\Services\CreditNoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function create(Bill $bill): View
    {
        $bill->load('items');

        return view('credit-notes.create', compact('bill'));
    }

    public function store(Request $request, Bill $bill, CreditNoteService $service): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $note = $service->create($bill, $data['qty'], $data['reason'], $request->user());

        return redirect()->route('credit-notes.show', $note)->with('status', 'Tax credit note issued.');
    }

    public function show(CreditNote $creditNote): View
    {
        $creditNote->load(['items.billItem', 'bill']);

        return view('credit-notes.show', compact('creditNote'));
    }

    public function pdf(CreditNote $creditNote): Response
    {
        $creditNote->load(['items.billItem', 'bill']);
        $pdf = Pdf::loadView('credit-notes.document', [
            'note' => $creditNote,
            'shop' => ShopSetting::current(),
        ]);

        return $pdf->download($creditNote->number.'.pdf');
    }
}
