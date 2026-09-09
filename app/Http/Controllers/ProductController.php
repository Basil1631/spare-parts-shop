<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Money;
use App\Support\Sku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->search($request->string('q')->toString())
            ->when(! $request->boolean('inactive'), fn ($q) => $q->active())
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Product::query()->create($data);

        return redirect()->route('products.index')->with('status', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product->id);
        $data['active'] = $request->boolean('active', true);
        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Product updated.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'sku_normalized' => Sku::normalize($request->input('sku')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100'],
            'sku_normalized' => ['required', Rule::unique('products', 'sku_normalized')->ignore($ignoreId)],
            'price' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_qty' => ['required', 'integer', 'min:0'],
        ]);

        $data['price_fils'] = Money::toFils($data['price']);
        unset($data['price']);
        $data['vat_rate'] = ($data['vat_rate'] === '' || $data['vat_rate'] === null) ? null : $data['vat_rate'];

        return $data;
    }
}
