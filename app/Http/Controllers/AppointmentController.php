<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:appointments.view')->only(['index', 'show', 'calendar']);
        $this->middleware('permission:appointments.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date, fn ($q) => $q->whereDate('scheduled_at', $request->date))
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    public function create(Request $request)
    {
        return view('appointments.create', [
            'patients' => Patient::orderBy('name')->get(),
            'doctors' => $this->doctors(),
            'parent' => $request->parent ? Appointment::find($request->parent) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // The clinic this appointment will belong to (super admins pick / use working clinic).
        $clinicId = auth()->user()->clinic_id ?: $request->input('clinic_id') ?: session('active_clinic_id');
        $this->assertPatientInClinic($data['patient_id'], $clinicId);
        $this->assertDoctorInClinic($data['doctor_id'] ?? null, $clinicId);
        $this->assertSlotAvailable($clinicId, $data['scheduled_at']);

        $treatment = DB::transaction(function () use ($data) {
            $appointment = Appointment::create($data + ['created_by' => auth()->id()]);

            // Booking a slot auto-creates the linked treatment where fees are added.
            return Treatment::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'treatment_date' => $appointment->scheduled_at->toDateString(),
            ]);
        });

        AuditLog::record('created', "Booked appointment (treatment #{$treatment->id})", $treatment);

        return redirect()->route('treatments.edit', $treatment)
            ->with('status', 'Appointment booked. Add the treatment charges below.');
    }

    /** Clicking an appointment anywhere opens its treatment. */
    public function show(Appointment $appointment)
    {
        $treatment = Treatment::where('appointment_id', $appointment->id)->first();
        if ($treatment) {
            return redirect()->route('treatments.edit', $treatment);
        }

        // Fallback for legacy appointments without a treatment: create one.
        $treatment = Treatment::create([
            'clinic_id' => $appointment->clinic_id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'treatment_date' => $appointment->scheduled_at->toDateString(),
        ]);

        return redirect()->route('treatments.edit', $treatment);
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit', [
            'appointment' => $appointment,
            'patients' => Patient::orderBy('name')->get(),
            'doctors' => $this->doctors(),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validateData($request);

        $this->assertPatientInClinic($data['patient_id'], $appointment->clinic_id);
        $this->assertDoctorInClinic($data['doctor_id'] ?? null, $appointment->clinic_id);
        $this->assertSlotAvailable($appointment->clinic_id, $data['scheduled_at'], $appointment->id);

        $appointment->update($data);
        // Keep the linked treatment's patient/doctor/date in sync.
        Treatment::where('appointment_id', $appointment->id)->update([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'treatment_date' => $appointment->scheduled_at->toDateString(),
        ]);

        AuditLog::record('updated', "Updated appointment {$appointment->appointment_no}", $appointment);

        return redirect()->route('appointments.index')->with('status', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment)
    {
        AuditLog::record('deleted', "Deleted appointment {$appointment->appointment_no}", $appointment);
        $appointment->delete();

        return redirect()->route('appointments.index')->with('status', 'Appointment deleted.');
    }

    /**
     * A patient can only be booked at their own clinic — this stops a super admin
     * from attaching a patient from a different clinic to the appointment (which
     * would later be invisible to that clinic's staff).
     */
    private function assertPatientInClinic($patientId, $clinicId): void
    {
        $patient = Patient::withoutGlobalScope('clinic')->find($patientId);
        if ($patient && $clinicId && (int) $patient->clinic_id !== (int) $clinicId) {
            throw ValidationException::withMessages([
                'patient_id' => 'This patient belongs to '.($patient->clinic->name ?? 'another clinic')
                    .'. Please choose a patient from the selected clinic.',
            ]);
        }
    }

    /** The assigned doctor must belong to the appointment's clinic. */
    private function assertDoctorInClinic($doctorId, $clinicId): void
    {
        if (! $doctorId) {
            return;
        }
        $doctor = Doctor::withoutGlobalScope('clinic')->find($doctorId);
        if ($doctor && $clinicId && (int) $doctor->clinic_id !== (int) $clinicId) {
            throw ValidationException::withMessages([
                'doctor_id' => 'This doctor belongs to another clinic. Please choose a doctor from the selected clinic.',
            ]);
        }
    }

    /**
     * Prevent double-booking: a clinic cannot have two active appointments at the
     * exact same date & time. Cancelled appointments free the slot again.
     */
    private function assertSlotAvailable($clinicId, string $scheduledAt, ?int $ignoreId = null): void
    {
        $dt = Carbon::parse($scheduledAt);

        $clash = Appointment::withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->whereBetween('scheduled_at', [$dt->copy()->startOfMinute(), $dt->copy()->endOfMinute()])
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if ($clash) {
            $clinicName = Clinic::find($clinicId)?->name ?? 'this clinic';
            throw ValidationException::withMessages([
                'scheduled_at' => $dt->format('j F Y g:i A').' already has an appointment ('
                    .$clash->appointment_no.') for '.$clinicName.'. Please choose a different time.',
            ]);
        }
    }

    /**
     * Month calendar view so doctors and staff can see appointments at a glance.
     */
    public function calendar(Request $request)
    {
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);

        $month = $request->month ? Carbon::parse($request->month.'-01') : Carbon::now()->startOfMonth();
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $appointments = Appointment::with(['patient', 'doctor'])
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->whereBetween('scheduled_at', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn ($a) => $a->scheduled_at->toDateString());

        return view('appointments.calendar', [
            'month' => $month,
            'gridStart' => $gridStart,
            'gridEnd' => $gridEnd,
            'appointments' => $appointments,
            'clinics' => $isSuper ? Clinic::orderBy('name')->get() : collect(),
            'clinicFilter' => $clinicFilter,
        ]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:booked,completed,cancelled,no_show',
            'reason' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:appointments,id',
            'reminder_at' => 'nullable|date',
            'assistance_note' => 'nullable|string',
            'client_uuid' => 'nullable|uuid',
        ]);
    }

    private function doctors()
    {
        // Global clinic scope already limits clinic users to their own doctors;
        // a super admin gets every clinic's doctors (filtered live on the form).
        return Doctor::where('is_active', true)->orderBy('name')->get();
    }
}
