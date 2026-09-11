<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class GodownReceiptController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canConfirmGodown(), 403);
        $purchases = Purchase::query()
            ->awaitingGodown()
            ->with(['items.product', 'branch', 'creator'])
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->latest()
            ->paginate(20);

        return view('godown.index', compact('purchases'));
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->canConfirmGodown(), 403);
        $this->assertBranch($request, $purchase);
        abort_unless($purchase->isAwaitingGodown(), 404);
        $purchase->load(['items.product', 'branch']);

        return view('godown.show', compact('purchase'));
    }

    public function confirm(Request $request, Purchase $purchase, PurchaseService $purchases): RedirectResponse
    {
        abort_unless($request->user()->canConfirmGodown(), 403);
        $this->assertBranch($request, $purchase);
        $data = $request->validate([
            'received' => ['required', 'array'],
            'received.*' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $purchases->confirmReceipt($purchase, array_map('intval', $data['received']), $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['received' => $e->getMessage()])->withInput();
        }

        return redirect()->route('godown.index')->with('status', 'Receipt confirmed. Stock updated. Invoice sent to accounts.');
    }

    private function assertBranch(Request $request, Purchase $purchase): void
    {
        if (! $request->user()->isAdmin() && $purchase->branch_id !== $request->user()->branch_id) {
            abort(403);
        }
    }
}
