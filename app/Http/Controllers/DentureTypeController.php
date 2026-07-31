<?php

namespace App\Http\Controllers;

use App\Models\DentureType;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DentureTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    public function index()
    {
        $dentureTypes = DentureType::with('supplier')->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('denture_types.index', compact('dentureTypes'));
    }

    public function create()
    {
        return view('denture_types.create', ['suppliers' => Supplier::withoutGlobalScope('clinic')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        DentureType::create($this->validated($request));

        return redirect()->route('denture-types.index')->with('status', 'Denture type created.');
    }

    public function edit(DentureType $dentureType)
    {
        return view('denture_types.edit', [
            'type' => $dentureType,
            'suppliers' => Supplier::withoutGlobalScope('clinic')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, DentureType $dentureType)
    {
        $dentureType->update($this->validated($request));

        return redirect()->route('denture-types.index')->with('status', 'Denture type updated.');
    }

    public function destroy(DentureType $dentureType)
    {
        $dentureType->delete();

        return redirect()->route('denture-types.index')->with('status', 'Denture type deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['price'] = $data['price'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
