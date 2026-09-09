<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockController extends Controller
{
    public function index(Request $request): View
    {
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

    public function receive(Request $request, Product $product, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $stock->receive($product, (int) $data['qty'], $request->user(), $data['note'] ?? 'Stock received');

        return back()->with('status', "Received {$data['qty']} of {$product->sku}.");
    }

    public function adjustOut(Request $request, Product $product, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $stock->adjustOut($product, (int) $data['qty'], $request->user(), $data['note']);

        return back()->with('status', "Adjusted out {$data['qty']} of {$product->sku}.");
    }

    public function exportLow(): StreamedResponse
    {
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
