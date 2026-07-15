<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:doctors.manage');
    }

    public function index(Request $request)
    {
        $doctors = Doctor::with('clinic')
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        return view('doctors.create');
    }

    public function store(Request $request)
    {
        Doctor::create($this->validated($request));

        return redirect()->route('doctors.index')->with('status', 'Doctor created.');
    }

    public function edit(Doctor $doctor)
    {
        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $doctor->update($this->validated($request));

        return redirect()->route('doctors.index')->with('status', 'Doctor updated.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')->with('status', 'Doctor deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'clinic_id' => 'nullable|exists:clinics,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'one_day_salary' => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        // clinic_id is auto-filled by BelongsToClinic for clinic users; a super
        // admin must supply it (via the form's clinic picker).
        if (auth()->user()->clinic_id) {
            unset($data['clinic_id']);
        }

        return $data;
    }
}
