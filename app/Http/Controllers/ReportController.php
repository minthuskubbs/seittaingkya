<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view');
    }

    public function index()
    {
        return view('reports.index');
    }

    public function patients(Request $request)
    {
        $query = Patient::withCount(['appointments', 'treatments'])->latest();

        if ($request->export === 'csv') {
            return $this->streamCsv('patients-'.date('Ymd-His').'.csv',
                ['Code', 'Name', 'Age', 'Phone', 'Address', 'Appointments', 'Treatments'],
                $query->get()->map(fn ($p) => [
                    $p->patient_code, $p->name, $p->age, $p->phone, $p->address,
                    $p->appointments_count, $p->treatments_count,
                ]));
        }

        $patients = $query->paginate(20)->withQueryString();

        return view('reports.patients', compact('patients'));
    }

    public function treatments(Request $request)
    {
        [$from, $to] = $this->range($request);

        $query = Treatment::with(['patient', 'doctor', 'treatmentTypes'])
            ->whereBetween('treatment_date', [$from, $to])
            ->when($request->doctor_id, fn ($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->patient_code, fn ($q) => $q->whereHas('patient', fn ($p) => $p->where('patient_code', 'like', "%{$request->patient_code}%")))
            ->when($request->treatment_type_id, fn ($q) => $q->whereHas('treatmentTypes', fn ($tt) => $tt->where('treatment_types.id', $request->treatment_type_id)))
            ->latest('treatment_date');

        if ($request->export === 'csv') {
            return $this->streamCsv('treatments-'.date('Ymd-His').'.csv',
                ['Date', 'Patient Code', 'Patient', 'Treatment Type', 'Qty', 'Doctor'],
                $query->get()->map(fn ($t) => [
                    $t->treatment_date?->format('Y-m-d'),
                    $t->patient->patient_code ?? '', $t->patient->name ?? '',
                    $t->treatmentTypes->pluck('name')->implode(', '),
                    $t->treatmentTypes->pluck('pivot.qty')->implode(', '),
                    $t->doctor->name ?? '',
                ]));
        }

        $treatments = $query->paginate(20)->withQueryString();

        return view('reports.treatments', [
            'treatments' => $treatments,
            'from' => $from, 'to' => $to,
            'doctors' => Doctor::orderBy('name')->get(),
            'treatmentTypes' => \App\Models\TreatmentType::active()->get(),
        ]);
    }

    /** Treatment list overview: by treatment type (Tx-name) with patient count + income. */
    public function treatmentList(Request $request)
    {
        [$from, $to] = $this->range($request);

        $treatments = Treatment::with('treatmentTypes')
            ->whereBetween('treatment_date', [$from, $to])
            ->get();

        // A treatment can have several types; count it under each of them.
        $byType = [];
        foreach ($treatments as $t) {
            $types = $t->treatmentTypes->isNotEmpty() ? $t->treatmentTypes->pluck('name')->all() : ['— No type —'];
            foreach ($types as $name) {
                $byType[$name] ??= ['name' => $name, 'patients' => [], 'treatment_count' => 0, 'total_income' => 0.0];
                $byType[$name]['patients'][$t->patient_id] = true;
                $byType[$name]['treatment_count']++;
                $byType[$name]['total_income'] += (float) $t->total_amount;
            }
        }

        $rows = collect($byType)->map(fn ($r) => [
            'name' => $r['name'],
            'patient_count' => count($r['patients']),
            'treatment_count' => $r['treatment_count'],
            'total_income' => $r['total_income'],
        ])->sortByDesc('total_income')->values();

        if ($request->export === 'csv') {
            return $this->streamCsv('treatment-list-'.date('Ymd-His').'.csv',
                ['Tx-Name', 'Patients', 'Treatments', 'Total Income'],
                $rows->map(fn ($r) => [$r['name'], $r['patient_count'], $r['treatment_count'], $r['total_income']]));
        }

        return view('reports.treatment_list', [
            'rows' => $rows,
            'from' => $from, 'to' => $to,
            // Distinct treatments, so a multi-type treatment isn't counted twice.
            'grandTotal' => (float) $treatments->sum('total_amount'),
        ]);
    }

    /** Daily sales report (medicine POS). */
    public function sales(Request $request)
    {
        [$from, $to] = $this->range($request);
        $sales = Sale::with('items')->whereBetween('sold_at', [$from, $to])->get();

        $daily = $sales->groupBy(fn ($s) => Carbon::parse($s->sold_at)->toDateString())
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ]);

        // Item breakdown: which products were sold, quantity and revenue.
        $items = $sales->flatMap->items
            ->groupBy('name')
            ->map(fn ($group, $name) => [
                'name' => $name,
                'quantity' => (int) $group->sum('quantity'),
                'total' => (float) $group->sum('line_total'),
            ])
            ->sortByDesc('total')
            ->values();

        if ($request->export === 'items') {
            return $this->streamCsv('sales-items-'.date('Ymd-His').'.csv',
                ['Product', 'Quantity Sold', 'Total'],
                $items->map(fn ($r) => [$r['name'], $r['quantity'], $r['total']]));
        }

        if ($request->export === 'csv') {
            return $this->streamCsv('sales-'.date('Ymd-His').'.csv',
                ['Date', 'Transactions', 'Total'],
                $daily->map(fn ($row, $date) => [$date, $row['count'], $row['total']])->values());
        }

        return view('reports.sales', [
            'daily' => $daily,
            'items' => $items,
            'from' => $from, 'to' => $to,
            'grandTotal' => $sales->sum('total'),
        ]);
    }

    public function inventory(Request $request)
    {
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);

        $products = Product::with(['supplier', 'clinic'])
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->orderBy('type')->orderBy('name')->get();

        if ($request->export === 'csv') {
            return $this->streamCsv('inventory-'.date('Ymd-His').'.csv',
                ['Product', 'Clinic', 'Type', 'Stock', 'Unit', 'Cost', 'Sale Price', 'Stock Value', 'Low Stock'],
                $products->map(fn ($p) => [
                    $p->name, $p->clinic->name ?? '', $p->type, $p->stock_qty, $p->unit,
                    $p->cost_price, $p->sale_price, $p->stock_qty * (float) $p->cost_price,
                    $p->isLowStock() ? 'Yes' : 'No',
                ]));
        }

        $stockValue = $products->sum(fn ($p) => $p->stock_qty * (float) $p->cost_price);
        $clinics = $isSuper ? \App\Models\Clinic::orderBy('name')->get() : collect();

        return view('reports.inventory', compact('products', 'stockValue', 'clinics', 'clinicFilter'));
    }

    /** Financial summary — only for finance viewers. */
    public function financial(Request $request, \App\Services\DoctorPayrollService $doctorPayroll)
    {
        abort_unless(auth()->user()->can('finance.view'), 403);
        [$from, $to] = $this->range($request);

        $revenue = (float) Payment::whereBetween('paid_at', [$from, $to])->sum('amount');
        $salesTotal = (float) Sale::whereBetween('sold_at', [$from, $to])->sum('total');
        $treatmentBilled = (float) \App\Models\Treatment::whereBetween('treatment_date', [$from, $to])->sum('total_amount');

        $manualExpenses = (float) \App\Models\Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        // Payroll (committed staff + doctor payroll) across the months touched.
        $payroll = 0;
        $cursor = $from->copy()->startOfMonth();
        while ($cursor <= $to) {
            $payroll += (float) \App\Models\StaffPayroll::withoutGlobalScope('clinic')
                ->where('year', $cursor->year)->where('month', $cursor->month)->sum('total');
            $payroll += $doctorPayroll->monthlyCommittedTotal(null, $cursor);
            $cursor->addMonth();
        }
        $expenses = $manualExpenses + $payroll;
        $net = $revenue - $expenses;

        if ($request->export === 'csv') {
            return $this->streamCsv('financial-'.date('Ymd-His').'.csv',
                ['Metric', 'Amount (MMK)'],
                [
                    ['Period', $from->format('Y-m-d').' to '.$to->format('Y-m-d')],
                    ['Revenue Collected', $revenue],
                    ['Medicine Sales', $salesTotal],
                    ['Treatment Billed', $treatmentBilled],
                    ['Manual Expenses', $manualExpenses],
                    ['Payroll (Salary + Commission)', $payroll],
                    ['Total Expenses', $expenses],
                    ['Net Profit', $net],
                ]);
        }

        return view('reports.financial', compact(
            'revenue', 'salesTotal', 'treatmentBilled', 'manualExpenses', 'payroll', 'expenses', 'net', 'from', 'to'
        ));
    }

    /** Stream a UTF-8 CSV (with BOM so Excel renders unicode correctly). */
    private function streamCsv(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, (array) $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function range(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();

        return [$from, $to];
    }
}
