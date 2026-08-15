<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:patients.view')->only(['index', 'show']);
        $this->middleware('permission:patients.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $patients = Patient::query()
            ->when($request->q, fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('name', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%")
                  ->orWhere('patient_code', 'like', "%{$request->q}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create', ['doctors' => $this->doctors()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $patient = Patient::create($data);
        AuditLog::record('created', "Created patient {$patient->name}", $patient);

        return redirect()->route('patients.show', $patient)->with('status', 'Patient created.');
    }

    public function show(Patient $patient)
    {
        $patient->load(['assignedDoctor', 'appointments.doctor', 'treatments.doctor', 'treatments.treatmentTypes', 'treatments.appointment', 'attachments', 'feedbacks.doctor', 'feedbacks.author']);
        $doctors = \App\Models\Doctor::where('is_active', true)->orderBy('name')->get();

        return view('patients.show', compact('patient', 'doctors'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', ['patient' => $patient, 'doctors' => $this->doctors()]);
    }

    public function update(Request $request, Patient $patient)
    {
        $patient->update($this->validateData($request));
        AuditLog::record('updated', "Updated patient {$patient->name}", $patient);

        return redirect()->route('patients.show', $patient)->with('status', 'Patient updated.');
    }

    public function destroy(Patient $patient)
    {
        AuditLog::record('deleted', "Deleted patient {$patient->name}", $patient);
        $patient->delete();

        return redirect()->route('patients.index')->with('status', 'Patient deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            // Patients belong to a clinic; the super admin picks which one (a
            // clinic-bound user inherits their own clinic automatically).
            'clinic_id' => 'nullable|exists:clinics,id',
            'name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:200',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'doctor_desc' => 'nullable|string',
            'assistance_desc' => 'nullable|string',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'medical_condition' => 'nullable|string',
            'drug_allergy' => 'nullable|string',
            'diabetes' => 'nullable|boolean',
            'hypertension' => 'nullable|boolean',
            'client_uuid' => 'nullable|uuid',
        ]) + ['diabetes' => $request->boolean('diabetes'), 'hypertension' => $request->boolean('hypertension')];
    }

    private function doctors()
    {
        return \App\Models\Doctor::where('is_active', true)->orderBy('name')->get();
    }
}
