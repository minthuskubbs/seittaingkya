<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Staff;
use App\Models\StaffPayroll;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffPayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request)
    {
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);
        $month = $request->month ? Carbon::parse($request->month.'-01') : Carbon::now()->startOfMonth();

        $staff = Staff::withoutGlobalScope('clinic')
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->where('is_active', true)->orderBy('name')->get();

        $saved = StaffPayroll::withoutGlobalScope('clinic')
            ->where('year', $month->year)->where('month', $month->month)
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->get()->keyBy('staff_id');

        $rows = $staff->map(fn ($s) => [
            'staff' => $s,
            'payroll' => $saved->get($s->id),
        ]);

        return view('finance.staff_payroll', [
            'rows' => $rows,
            'month' => $month,
            'clinicFilter' => $clinicFilter,
            'clinics' => $isSuper ? Clinic::orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|date_format:Y-m',
            'clinic_id' => 'nullable|exists:clinics,id',
            'rows' => 'array',
            'rows.*.basic_salary' => 'nullable|numeric|min:0',
            'rows.*.bonus' => 'nullable|numeric|min:0',
            'rows.*.attendance_allowance' => 'nullable|numeric|min:0',
            'rows.*.transportation_allowance' => 'nullable|numeric|min:0',
        ]);

        $month = Carbon::parse($data['month'].'-01');

        foreach (($data['rows'] ?? []) as $staffId => $row) {
            $staff = Staff::withoutGlobalScope('clinic')->find($staffId);
            if (! $staff) {
                continue;
            }
            $basic = (float) ($row['basic_salary'] ?? 0);
            $bonus = (float) ($row['bonus'] ?? 0);
            $attendance = (float) ($row['attendance_allowance'] ?? 0);
            $transport = (float) ($row['transportation_allowance'] ?? 0);

            StaffPayroll::withoutGlobalScope('clinic')->updateOrCreate(
                ['staff_id' => $staff->id, 'year' => $month->year, 'month' => $month->month],
                [
                    'clinic_id' => $staff->clinic_id,
                    'basic_salary' => $basic,
                    'bonus' => $bonus,
                    'attendance_allowance' => $attendance,
                    'transportation_allowance' => $transport,
                    'total' => $basic + $bonus + $attendance + $transport,
                    'created_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('finance.staff_payroll', array_filter([
            'month' => $data['month'], 'clinic_id' => $data['clinic_id'] ?? null,
        ]))->with('status', 'Staff payroll saved.');
    }
}
