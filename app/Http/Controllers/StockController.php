<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\LowStockNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function __construct()
    {
        // Anyone who can view inventory or do stock-out reaches this screen.
        $this->middleware('permission:inventory.view|inventory.stockout');
    }

    /** True when the user may also do stock-in / adjustments (super admin). */
    private function canManage(): bool
    {
        return auth()->user()->can('inventory.manage');
    }

    public function index(Request $request)
    {
        // Per-clinic stock: super admins default to their working clinic and can
        // switch; clinic staff only ever see their own clinic's products.
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);

        $products = Product::with('clinic')
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->orderBy('name')
            ->get();

        // Recent movements — who changed the stock, when, and how much.
        $movements = StockMovement::with(['product', 'creator'])
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->latest()->limit(30)->get();

        $clinics = $isSuper ? \App\Models\Clinic::orderBy('name')->get() : collect();

        return view('stock.index', [
            'products' => $products,
            'movements' => $movements,
            'clinics' => $clinics,
            'clinicFilter' => $clinicFilter,
            'canManage' => $this->canManage(),
        ]);
    }

    public function store(Request $request, LowStockNotifier $notifier)
    {
        // Stock-out-only users can never do stock-in or adjustments.
        $allowedDirections = $this->canManage() ? ['in', 'out', 'adjustment'] : ['out'];

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'direction' => ['required', Rule::in($allowedDirections)],
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $signed = $data['direction'] === 'out' ? -$data['quantity'] : $data['quantity'];

        if ($signed < 0 && $product->stock_qty + $signed < 0) {
            return back()->withErrors(['quantity' => 'Not enough stock on hand.'])->withInput();
        }

        // adjustStock() records the movement with created_by + timestamp.
        $product->adjustStock($signed, $data['direction'], $data['reference'] ?? null, $data['note'] ?? null);

        if ($product->isLowStock()) {
            $notifier->notify($product);
        }

        return back()->with('status', "Stock {$data['direction']} recorded for {$product->name}.");
    }
}
