<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPayroll;
use App\Models\Treatment;
use Illuminate\Support\Carbon;

/**
 * Monthly doctor payroll.
 *
 * Formula (per the client's worked example):
 *   basic_salary    = one_day_salary * days_worked
 *   total_income    = treatment_fees_total + lab_fees (denture)
 *   commission_base = total_income - (2 * basic_salary) - lab_fees
 *   commission      = commission_base * commission_percent / 100
 *   doctor_total    = basic_salary + commission
 *
 * total_income is Treatment Fees plus lab (denture) work; Services Fees and
 * medicine sales are excluded. lab_fees is the denture total on the doctor's
 * treatments for the month and is deducted so commission is not paid on lab
 * work. days_worked is entered by the admin.
 *
 * Example — Dr Kyaw, 12 days, one-day salary 100,000, 45%:
 *   basic = 1,200,000; total_income = 11,000,000 (incl. 1,200,000 lab);
 *   base  = 11,000,000 - 2,400,000 - 1,200,000 = 7,400,000;
 *   commission = 3,330,000; total = 4,530,000.
 */
class DoctorPayrollService
{
    /** Compute the numbers for one doctor + month given a days_worked value. */
    public function compute(Doctor $doctor, ?int $clinicId, Carbon $month, int $daysWorked): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth()->endOfDay();

        // Total income = Treatment Fees (dentist/extraction/implant/surgery/
        // additional/treatment-types) + lab (denture) work. Services Fees and
        // medicine sales are excluded. Lab fees (denture) are then deducted from
        // the commission base so commission is not paid on lab work.
        $treatments = Treatment::withoutGlobalScope('clinic')
            ->with(['fees', 'treatmentTypes'])
            ->where('doctor_id', $doctor->id)
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->whereBetween('treatment_date', [$start, $end])
            ->get();

        $treatmentFeesTotal = (float) $treatments->sum(fn ($t) => $t->treatmentFeesTotal());
        $labFees = (float) $treatments->sum('denture_charge');
        $totalIncome = $treatmentFeesTotal + $labFees;

        $oneDaySalary = (float) $doctor->one_day_salary;
        $commissionPercent = (float) $doctor->commission_percent;

        $basicSalary = $oneDaySalary * $daysWorked;
        $commissionBase = max(0, $totalIncome - (2 * $basicSalary) - $labFees);
        $commission = round($commissionBase * $commissionPercent / 100, 2);
        $total = $basicSalary + $commission;

        return [
            'days_worked' => $daysWorked,
            'one_day_salary' => $oneDaySalary,
            'commission_percent' => $commissionPercent,
            'treatment_fees_total' => $treatmentFeesTotal,
            'lab_fees' => $labFees,
            'total_income' => $totalIncome,
            'denture_total' => $labFees,
            'basic_salary' => $basicSalary,
            'commission_base' => $commissionBase,
            'commission' => $commission,
            'total' => $total,
        ];
    }

    /**
     * Build rows for every doctor in a clinic for a month, using the saved
     * days_worked (from a prior save) or 0 when nothing has been entered yet.
     */
    public function forMonth(?int $clinicId, Carbon $month): array
    {
        $doctors = Doctor::withoutGlobalScope('clinic')
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $saved = DoctorPayroll::withoutGlobalScope('clinic')
            ->where('year', $month->year)->where('month', $month->month)
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->get()->keyBy('doctor_id');

        $rows = [];
        foreach ($doctors as $doctor) {
            $days = (int) ($saved->get($doctor->id)->days_worked ?? 0);
            $rows[] = ['doctor' => $doctor, 'saved' => $saved->has($doctor->id)]
                + $this->compute($doctor, $clinicId, $month, $days);
        }

        return $rows;
    }

    /** Total committed doctor payroll for a clinic + month (used in finance net). */
    public function monthlyCommittedTotal(?int $clinicId, Carbon $month): float
    {
        return (float) DoctorPayroll::withoutGlobalScope('clinic')
            ->where('year', $month->year)->where('month', $month->month)
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->sum('total');
    }
}
