<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        // Product catalogue + stock-in are managed by the super admin only.
        $this->middleware('permission:inventory.manage');
    }

    public function index(Request $request)
    {
        // Super admins see every clinic; default the view to their working clinic
        // so inventory always reads as per-clinic rather than a merged pool.
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);

        $products = Product::with(['supplier', 'clinic'])
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->when($request->boolean('low'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $clinics = $isSuper ? \App\Models\Clinic::orderBy('name')->get() : collect();

        return view('products.index', compact('products', 'clinics', 'clinicFilter'));
    }

    public function create()
    {
        return view('products.create', ['suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $initial = (int) ($data['stock_qty'] ?? 0);
        $data['stock_qty'] = 0;
        $product = Product::create($data);
        if ($initial > 0) {
            $product->adjustStock($initial, 'in', 'Opening stock');
        }

        return redirect()->route('products.index')->with('status', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['supplier', 'movements' => fn ($q) => $q->latest()->limit(50)]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        unset($data['stock_qty']); // stock changes go through stock in/out, not edit
        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'type' => 'required|in:medicine,dental_supply',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_qty' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
