<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Fee;
use App\Models\Patient;
use App\Models\Procedure;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clinical.view')->only(['index', 'show']);
        $this->middleware('permission:clinical.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $treatments = Treatment::with(['patient', 'doctor', 'procedure', 'appointment'])
            ->when($request->patient_id, fn ($q) => $q->where('patient_id', $request->patient_id))
            ->latest('treatment_date')
            ->paginate(15)
            ->withQueryString();

        return view('treatments.index', compact('treatments'));
    }

    public function create(Request $request)
    {
        return view('treatments.create', $this->formData(null) + [
            'patient' => $request->patient_id ? Patient::find($request->patient_id) : null,
        ]);
    }

    public function store(Request $request)
    {
        $treatment = DB::transaction(function () use ($request) {
            $treatment = Treatment::create($this->validated($request));
            $treatment->treatmentTypes()->sync($request->input('treatment_types', []));
            $this->syncFees($treatment, $request->input('fees', []));
            $treatment->recalculateTotal();

            return $treatment;
        });
        AuditLog::record('created', 'Created treatment record', $treatment);

        return redirect()->route('treatments.edit', $treatment)->with('status', 'Treatment saved.');
    }

    public function show(Treatment $treatment)
    {
        $treatment->load(['patient', 'doctor', 'procedure', 'fees', 'payments', 'sales.items', 'appointment', 'treatmentTypes']);

        return view('treatments.show', compact('treatment'));
    }

    public function edit(Treatment $treatment)
    {
        $treatment->load(['fees', 'payments', 'sales', 'treatmentTypes']);

        return view('treatments.edit', $this->formData($treatment) + ['treatment' => $treatment]);
    }

    public function update(Request $request, Treatment $treatment)
    {
        DB::transaction(function () use ($request, $treatment) {
            $treatment->update($this->validated($request));
            $treatment->treatmentTypes()->sync($request->input('treatment_types', []));
            if ($request->has('fees')) {
                $treatment->fees()->delete();
                $this->syncFees($treatment, $request->input('fees', []));
            }
            $treatment->recalculateTotal();
        });
        AuditLog::record('updated', 'Updated treatment record', $treatment);

        return redirect()->route('treatments.edit', $treatment)->with('status', 'Treatment updated.');
    }

    public function destroy(Treatment $treatment)
    {
        $pid = $treatment->patient_id;
        $treatment->delete();

        return redirect()->route('patients.show', $pid)->with('status', 'Treatment deleted.');
    }

    /** Snapshot selected catalogue fees onto the treatment at current price. */
    private function syncFees(Treatment $treatment, array $feeIds): void
    {
        foreach (Fee::whereIn('id', $feeIds)->get() as $fee) {
            $price = $fee->effectivePrice();
            $treatment->fees()->create([
                'fee_id' => $fee->id,
                'name' => $fee->name,
                'category' => $fee->category,
                'price' => $price,
                'is_foc' => $fee->is_foc,
                'quantity' => 1,
                'line_total' => $price,
            ]);
        }
    }

    private function formData(?Treatment $treatment): array
    {
        // Fees for the treatment's clinic (or working clinic for a new record).
        $clinicId = $treatment?->clinic_id
            ?? (auth()->user()->clinic_id ?: session('active_clinic_id'));

        return [
            'patients' => Patient::orderBy('name')->get(),
            'procedures' => Procedure::where('is_active', true)->orderBy('name')->get(),
            'treatmentTypes' => \App\Models\TreatmentType::active()->get(),
            'doctors' => Doctor::where('is_active', true)->orderBy('name')->get(),
            'fees' => Fee::where('is_active', true)
                ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
                ->orderBy('category')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'procedure_id' => 'nullable|exists:procedures,id',
            'tooth' => 'nullable|string|max:50',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'treatment_date' => 'required|date',
            // Billing
            'denture_charge' => 'nullable|numeric|min:0',
            'surgery_charge' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'extraction_price' => 'nullable|numeric|min:0',
            'extraction_qty' => 'nullable|integer|min:0',
            'implant_price' => 'nullable|numeric|min:0',
            'implant_qty' => 'nullable|integer|min:0',
        ]);
    }
}
