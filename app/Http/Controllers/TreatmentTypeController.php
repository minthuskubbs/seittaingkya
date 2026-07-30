<?php

namespace App\Http\Controllers;

use App\Models\TreatmentType;
use Illuminate\Http\Request;

class TreatmentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    public function index()
    {
        $treatmentTypes = TreatmentType::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('treatment_types.index', compact('treatmentTypes'));
    }

    public function create()
    {
        return view('treatment_types.create');
    }

    public function store(Request $request)
    {
        TreatmentType::create($this->validated($request));

        return redirect()->route('treatment-types.index')->with('status', 'Treatment type created.');
    }

    public function edit(TreatmentType $treatmentType)
    {
        return view('treatment_types.edit', compact('treatmentType'));
    }

    public function update(Request $request, TreatmentType $treatmentType)
    {
        $treatmentType->update($this->validated($request));

        return redirect()->route('treatment-types.index')->with('status', 'Treatment type updated.');
    }

    public function destroy(TreatmentType $treatmentType)
    {
        $treatmentType->delete();

        return redirect()->route('treatment-types.index')->with('status', 'Treatment type deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'require_qty' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['require_qty'] = $request->boolean('require_qty', true);
        $data['price'] = $data['price'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
