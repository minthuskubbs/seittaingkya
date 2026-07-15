<?php

namespace App\Http\Controllers;

use App\Models\SaleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleTypeController extends Controller
{
    public function __construct()
    {
        // Sale types are maintained by the super admin only.
        $this->middleware('role:super_admin');
    }

    public function index()
    {
        $saleTypes = SaleType::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('sale_types.index', compact('saleTypes'));
    }

    public function create()
    {
        return view('sale_types.create');
    }

    public function store(Request $request)
    {
        SaleType::create($this->validated($request));

        return redirect()->route('sale-types.index')->with('status', 'Sale type created.');
    }

    public function edit(SaleType $saleType)
    {
        return view('sale_types.edit', compact('saleType'));
    }

    public function update(Request $request, SaleType $saleType)
    {
        $saleType->update($this->validated($request, $saleType));

        return redirect()->route('sale-types.index')->with('status', 'Sale type updated.');
    }

    public function destroy(SaleType $saleType)
    {
        // Keep historical sales readable: block deleting a type still in use.
        if (\App\Models\Sale::where('sale_type', $saleType->slug)->exists()) {
            return back()->withErrors(['delete' => 'This sale type is used by existing sales. Deactivate it instead.']);
        }
        $saleType->delete();

        return redirect()->route('sale-types.index')->with('status', 'Sale type deleted.');
    }

    private function validated(Request $request, ?SaleType $saleType = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/', Rule::unique('sale_types')->ignore($saleType?->id)],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
