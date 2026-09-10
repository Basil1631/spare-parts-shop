<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchProductPrice;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FloorPriceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'branch_manager']), 403);
        $branchId = $request->user()->isAdmin()
            ? (int) $request->integer('branch_id', Branch::query()->value('id') ?: 0)
            : (int) $request->user()->branch_id;

        $products = Product::query()->active()->orderBy('name')->get();
        $prices = BranchProductPrice::query()->where('branch_id', $branchId)->get()->keyBy('product_id');

        return view('pricing.index', [
            'products' => $products,
            'prices' => $prices,
            'branchId' => $branchId,
            'branches' => Branch::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'branch_manager']), 403);
        $branchId = $request->user()->isAdmin()
            ? (int) $request->integer('branch_id')
            : (int) $request->user()->branch_id;

        $data = $request->validate([
            'profit' => ['required', 'array'],
            'profit.*' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ]);

        foreach ($data['profit'] as $productId => $percent) {
            if ($percent === null || $percent === '') {
                continue;
            }
            $row = BranchProductPrice::forProduct($branchId, (int) $productId);
            $row->profit_percent = (float) $percent;
            $row->recalculateFloor();
            $row->save();
        }

        return back()->with('status', 'Profit % and floor prices saved. Sales staff cannot sell below floor.');
    }
}
