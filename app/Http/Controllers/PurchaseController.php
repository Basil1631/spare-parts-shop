<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canPurchase(), 403);
        $purchases = Purchase::query()
            ->with(['branch', 'creator'])
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->latest()
            ->paginate(25);

        return view('purchases.index', compact('purchases'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canPurchase(), 403);

        return view('purchases.create', [
            'products' => Product::query()->active()->orderBy('name')->get(),
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PurchaseService $purchases): RedirectResponse
    {
        abort_unless($request->user()->canPurchase(), 403);
        $data = $request->validate([
            'branch_id' => [$request->user()->isAdmin() ? 'required' : 'nullable', 'exists:branches,id'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'purchased_on' => ['required', 'date'],
            'default_profit_percent' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'invoice_scan' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,webp'],
            'product_id' => ['required', 'array'],
            'product_id.*' => ['nullable', 'exists:products,id'],
            'qty' => ['required', 'array'],
            'unit_cost' => ['required', 'array'],
        ]);

        $lines = [];
        foreach ($data['product_id'] as $i => $productId) {
            if (! $productId) {
                continue;
            }
            $lines[] = [
                'product_id' => (int) $productId,
                'qty' => (int) ($data['qty'][$i] ?? 0),
                'unit_cost' => $data['unit_cost'][$i] ?? 0,
            ];
        }

        try {
            $purchase = $purchases->create(
                [
                    'branch_id' => $request->user()->isAdmin() ? $data['branch_id'] : $request->user()->branch_id,
                    'supplier_name' => $data['supplier_name'] ?? null,
                    'invoice_number' => $data['invoice_number'] ?? null,
                    'purchased_on' => $data['purchased_on'],
                    'default_profit_percent' => $data['default_profit_percent'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                ],
                $lines,
                $request->user(),
                $request->file('invoice_scan'),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['lines' => $e->getMessage()])->withInput();
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Purchase recorded. Stock and floor cost updated.');
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->canPurchase(), 403);
        if (! $request->user()->isAdmin() && $purchase->branch_id !== $request->user()->branch_id) {
            abort(403);
        }
        $purchase->load(['items.product', 'branch', 'creator']);

        return view('purchases.show', compact('purchase'));
    }
}
