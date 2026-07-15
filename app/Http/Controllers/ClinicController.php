<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClinicController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clinics.manage');
    }

    public function index()
    {
        $clinics = Clinic::withCount(['users', 'patients'])->orderBy('name')->get();

        return view('clinics.index', compact('clinics'));
    }

    public function create()
    {
        return view('clinics.create');
    }

    public function store(Request $request)
    {
        Clinic::create($this->validated($request));

        return redirect()->route('clinics.index')->with('status', 'Clinic created.');
    }

    public function edit(Clinic $clinic)
    {
        return view('clinics.edit', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $clinic->update($this->validated($request, $clinic));

        return redirect()->route('clinics.index')->with('status', 'Clinic updated.');
    }

    private function validated(Request $request, ?Clinic $clinic = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('clinics')->ignore($clinic?->id)],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
