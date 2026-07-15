<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPayroll;
use App\Models\Treatment;
use Illuminate\Support\Carbon;

/**
 * Monthly doctor payroll.
 *
 * Formula (per the client's example):
 *   basic_salary    = one_day_salary * days_worked
 *   commission_base = total_appointment_income - (2 * basic_salary) - denture_total
 *   commission      = commission_base * commission_percent / 100
 *   doctor_total    = basic_salary + commission
 *
 * total_appointment_income and denture_total are summed automatically from the
 * doctor's non-cancelled appointments in the month; days_worked is entered by
 * the admin.
 */
class DoctorPayrollService
{
    /** Compute the numbers for one doctor + month given a days_worked value. */
    public function compute(Doctor $doctor, ?int $clinicId, Carbon $month, int $daysWorked): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth()->endOfDay();

        // Billing lives on treatments now: total_amount is the full charges (incl.
        // denture); denture is then deducted once in the commission base.
        $treatments = Treatment::withoutGlobalScope('clinic')
            ->where('doctor_id', $doctor->id)
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->whereBetween('treatment_date', [$start, $end]);

        $totalIncome = (float) (clone $treatments)->sum('total_amount');
        $dentureTotal = (float) (clone $treatments)->sum('denture_charge');

        $oneDaySalary = (float) $doctor->one_day_salary;
        $commissionPercent = (float) $doctor->commission_percent;

        $basicSalary = $oneDaySalary * $daysWorked;
        $commissionBase = max(0, $totalIncome - (2 * $basicSalary) - $dentureTotal);
        $commission = round($commissionBase * $commissionPercent / 100, 2);
        $total = $basicSalary + $commission;

        return [
            'days_worked' => $daysWorked,
            'one_day_salary' => $oneDaySalary,
            'commission_percent' => $commissionPercent,
            'total_income' => $totalIncome,
            'denture_total' => $dentureTotal,
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
