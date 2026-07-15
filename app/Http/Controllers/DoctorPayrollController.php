<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorPayroll;
use App\Services\DoctorPayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DoctorPayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request, DoctorPayrollService $service)
    {
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);
        $month = $request->month ? Carbon::parse($request->month.'-01') : Carbon::now()->startOfMonth();

        return view('finance.doctor_payroll', [
            'rows' => $service->forMonth($clinicFilter ? (int) $clinicFilter : null, $month),
            'month' => $month,
            'clinicFilter' => $clinicFilter,
            'clinics' => $isSuper ? Clinic::orderBy('name')->get() : collect(),
        ]);
    }

    /** Save days_worked per doctor and snapshot the computed payroll. */
    public function store(Request $request, DoctorPayrollService $service)
    {
        $data = $request->validate([
            'month' => 'required|date_format:Y-m',
            'clinic_id' => 'nullable|exists:clinics,id',
            'days' => 'array',
            'days.*' => 'nullable|integer|min:0|max:31',
        ]);

        $month = Carbon::parse($data['month'].'-01');
        $clinicId = $data['clinic_id'] ?? null;

        foreach (($data['days'] ?? []) as $doctorId => $daysWorked) {
            $doctor = Doctor::withoutGlobalScope('clinic')->find($doctorId);
            if (! $doctor) {
                continue;
            }
            $c = $service->compute($doctor, (int) $doctor->clinic_id, $month, (int) $daysWorked);

            DoctorPayroll::withoutGlobalScope('clinic')->updateOrCreate(
                ['doctor_id' => $doctor->id, 'year' => $month->year, 'month' => $month->month],
                [
                    'clinic_id' => $doctor->clinic_id,
                    'days_worked' => $c['days_worked'],
                    'one_day_salary' => $c['one_day_salary'],
                    'commission_percent' => $c['commission_percent'],
                    'total_income' => $c['total_income'],
                    'denture_total' => $c['denture_total'],
                    'basic_salary' => $c['basic_salary'],
                    'commission' => $c['commission'],
                    'total' => $c['total'],
                    'created_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('finance.doctor_payroll', array_filter([
            'month' => $data['month'], 'clinic_id' => $clinicId,
        ]))->with('status', 'Doctor payroll saved.');
    }
}
