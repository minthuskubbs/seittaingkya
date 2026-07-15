<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\StaffPayroll;
use App\Services\DoctorPayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:finance.view');
    }

    /** Revenue report: income (payments + manual) minus expenses (manual + payroll) = net. */
    public function revenue(Request $request, DoctorPayrollService $doctorPayroll)
    {
        [$from, $to, $clinicId] = $this->filters($request);

        $payments = Payment::query()
            ->whereBetween('paid_at', [$from, $to])
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId));

        $byMethod = (clone $payments)->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')->pluck('total', 'method');

        $totalPayments = (float) (clone $payments)->sum('amount');
        $manualIncome = (float) Income::query()->allClinics()
            ->whereBetween('income_date', [$from, $to])
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->sum('amount');

        // Expenses out: manual expenses in range + payroll (base salary + commission).
        $manualExpenses = (float) Expense::query()->allClinics()
            ->whereBetween('expense_date', [$from, $to])
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->sum('amount');
        $payrollTotal = $this->payrollForRange($doctorPayroll, $clinicId, $from, $to);

        $income = $totalPayments + $manualIncome;
        $expenses = $manualExpenses + $payrollTotal;

        return view('finance.revenue', [
            'from' => $from, 'to' => $to, 'clinicId' => $clinicId,
            'clinics' => Clinic::orderBy('name')->get(),
            'byMethod' => $byMethod,
            'totalPayments' => $totalPayments,
            'manualIncome' => $manualIncome,
            'income' => $income,
            'manualExpenses' => $manualExpenses,
            'payrollTotal' => $payrollTotal,
            'expenses' => $expenses,
            'net' => $income - $expenses,
        ]);
    }

    /** Committed staff + doctor payroll across the months the range touches. */
    private function payrollForRange(DoctorPayrollService $doctorPayroll, $clinicId, Carbon $from, Carbon $to): float
    {
        $total = 0;
        $cid = $clinicId ? (int) $clinicId : null;
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $total += $this->staffPayrollTotal($cid, $cursor);
            $total += $doctorPayroll->monthlyCommittedTotal($cid, $cursor);
            $cursor->addMonth();
        }

        return $total;
    }

    /** Committed staff payroll total for a clinic + month. */
    private function staffPayrollTotal(?int $clinicId, Carbon $month): float
    {
        return (float) StaffPayroll::withoutGlobalScope('clinic')
            ->where('year', $month->year)->where('month', $month->month)
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->sum('total');
    }

    /** Outstanding payments: treatments whose payments < invoice total. */
    public function outstanding(Request $request)
    {
        $clinicId = $request->clinic_id;

        $treatments = \App\Models\Treatment::query()
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->with(['patient', 'payments', 'fees', 'sales'])
            ->get()
            ->filter(fn ($t) => $t->balance() > 0.001)
            ->values();

        return view('finance.outstanding', [
            'treatments' => $treatments,
            'clinics' => Clinic::orderBy('name')->get(),
            'clinicId' => $clinicId,
            'totalOutstanding' => $treatments->sum(fn ($t) => $t->balance()),
        ]);
    }

    private function filters(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        return [$from, $to, $request->clinic_id];
    }
}
