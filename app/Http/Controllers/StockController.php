<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'branch_manager', 'purchase']), 403);
        $lowOnly = $request->string('filter')->toString() === 'low';

        $products = Product::query()
            ->active()
            ->search($request->string('q')->toString())
            ->when($lowOnly, fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('stock.index', [
            'products' => $products,
            'lowOnly' => $lowOnly,
        ]);
    }

    public function receive(Request $request, Product $product, StockService $stock, PurchaseService $purchases): RedirectResponse
    {
        abort_unless($request->user()->canPurchase(), 403);
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'invoice_scan' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $user = $request->user();
        $hasInvoice = $request->hasFile('invoice_scan') || filled($data['unit_cost'] ?? null);

        if ($hasInvoice) {
            try {
                $purchases->create(
                    [
                        'branch_id' => $user->branch_id ?: \App\Models\Branch::query()->value('id'),
                        'supplier_name' => null,
                        'invoice_number' => null,
                        'purchased_on' => now()->toDateString(),
                        'default_profit_percent' => 0,
                        'notes' => $data['note'] ?? 'Restock '.$product->sku,
                    ],
                    [[
                        'product_id' => $product->id,
                        'qty' => (int) $data['qty'],
                        'unit_cost' => $data['unit_cost'] ?? 0,
                    ]],
                    $user,
                    $request->file('invoice_scan'),
                );
            } catch (RuntimeException $e) {
                return back()->withErrors(['qty' => $e->getMessage()]);
            }

            return back()->with('status', "Restocked {$data['qty']} of {$product->sku}. Quantity updated for everyone. No new product was created.");
        }

        $stock->receive($product, (int) $data['qty'], $user, $data['note'] ?? 'Stock received');

        return back()->with('status', "Received {$data['qty']} of {$product->sku}. On-hand quantity is now {$product->fresh()->qty_on_hand}.");
    }

    public function adjustOut(Request $request, Product $product, StockService $stock): RedirectResponse
    {
        abort_unless($request->user()->canPurchase(), 403);
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $stock->adjustOut($product, (int) $data['qty'], $request->user(), $data['note']);

        return back()->with('status', "Adjusted out {$data['qty']} of {$product->sku}.");
    }

    public function exportLow(): StreamedResponse
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'branch_manager', 'purchase']), 403);
        $filename = 'low-stock-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'SKU', 'Current qty', 'Minimum qty', 'Suggested order qty', 'Price AED']);

            Product::query()->active()->lowStock()->orderBy('name')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $product) {
                    fputcsv($out, [
                        $product->name,
                        $product->sku,
                        $product->qty_on_hand,
                        $product->min_qty,
                        $product->suggestedOrderQty(),
                        number_format($product->price_fils / 100, 2, '.', ''),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
