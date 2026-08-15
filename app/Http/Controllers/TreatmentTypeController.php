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

    public function index(Request $request)
    {
        // Types are per-clinic (each clinic sets its own prices). Default to the
        // super admin's working clinic; a dropdown switches between clinics.
        $clinicFilter = $request->input('clinic_id', session('active_clinic_id'));

        $treatmentTypes = TreatmentType::with('clinic')
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->orderBy('clinic_id')->orderBy('sort_order')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $clinics = \App\Models\Clinic::orderBy('name')->get();

        return view('treatment_types.index', compact('treatmentTypes', 'clinics', 'clinicFilter'));
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
            // Types belong to a clinic; the super admin picks which one.
            'clinic_id' => 'required|exists:clinics,id',
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
