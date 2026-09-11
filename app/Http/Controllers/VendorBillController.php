<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class VendorBillController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canSeeVendorBills(), 403);
        $purchases = Purchase::query()
            ->forAccountant()
            ->with(['items.product', 'branch', 'receiver'])
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->latest('received_at')
            ->paginate(25);

        return view('vendor-bills.index', compact('purchases'));
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->canSeeVendorBills(), 403);
        $this->assertBranch($request, $purchase);
        abort_unless(in_array($purchase->status, [PurchaseStatus::Received, PurchaseStatus::Paid], true), 404);
        $purchase->load(['items.product', 'branch', 'creator', 'receiver', 'payer']);

        return view('vendor-bills.show', compact('purchase'));
    }

    public function markPaid(Request $request, Purchase $purchase, PurchaseService $purchases): RedirectResponse
    {
        abort_unless($request->user()->canSeeVendorBills(), 403);
        $this->assertBranch($request, $purchase);
        try {
            $purchases->markPaid($purchase, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Vendor bill marked paid.');
    }

    public function markUnpaid(Request $request, Purchase $purchase, PurchaseService $purchases): RedirectResponse
    {
        abort_unless($request->user()->canSeeVendorBills(), 403);
        $this->assertBranch($request, $purchase);
        try {
            $purchases->markUnpaid($purchase);
        } catch (RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Vendor bill marked unpaid.');
    }

    private function assertBranch(Request $request, Purchase $purchase): void
    {
        if (! $request->user()->isAdmin() && $purchase->branch_id !== $request->user()->branch_id) {
            abort(403);
        }
    }
}
