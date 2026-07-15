<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clinical.view')->only(['index', 'show']);
        $this->middleware('permission:clinical.manage')->only(['create', 'store', 'destroy']);
    }

    public function index(Request $request)
    {
        $prescriptions = Prescription::with(['patient', 'doctor', 'items'])
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->latest('prescribed_date')
            ->paginate(15)
            ->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        return view('prescriptions.create', [
            'patients' => Patient::orderBy('name')->get(),
            'products' => Product::where('type', 'medicine')->orderBy('name')->get(),
            'doctors' => \App\Models\Doctor::where('is_active', true)->orderBy('name')->get(),
            'patient' => $request->patient_id ? Patient::find($request->patient_id) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'prescribed_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string|max:255',
            'items.*.dosage' => 'nullable|string|max:255',
            'items.*.frequency' => 'nullable|string|max:255',
            'items.*.duration' => 'nullable|string|max:255',
            'items.*.instructions' => 'nullable|string',
        ]);

        $prescription = DB::transaction(function () use ($data) {
            $p = Prescription::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'prescribed_date' => $data['prescribed_date'],
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['items'] as $item) {
                $p->items()->create($item);
            }

            return $p;
        });

        return redirect()->route('prescriptions.show', $prescription)->with('status', 'Prescription saved.');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'items']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function destroy(Prescription $prescription)
    {
        $pid = $prescription->patient_id;
        $prescription->delete();

        return redirect()->route('patients.show', $pid)->with('status', 'Prescription deleted.');
    }
}
