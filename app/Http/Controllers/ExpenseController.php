<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExpenseController extends Controller
{
    public function __construct()
    {
        // Finance data is restricted to super admins (finance.view).
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request)
    {
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);
        $month = $request->month ? Carbon::parse($request->month.'-01') : Carbon::now()->startOfMonth();

        $expenses = Expense::with(['type', 'staff', 'doctor', 'supplier'])
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()->endOfDay()])
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        $total = (float) Expense::query()
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()->endOfDay()])
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'total' => $total,
            'month' => $month,
            'clinicFilter' => $clinicFilter,
            'clinics' => $isSuper ? Clinic::orderBy('name')->get() : collect(),
            'types' => ExpenseType::active()->get(),
            'staff' => User::when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))->orderBy('name')->get(),
            'doctors' => \App\Models\Doctor::withoutGlobalScope('clinic')
                ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
                ->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => \App\Models\Supplier::withoutGlobalScope('clinic')
                ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
                ->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'expense_type_id' => 'nullable|exists:expense_types,id',
            'user_id' => 'nullable|exists:users,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        Expense::create($data);

        return back()->with('status', 'Expense recorded.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', 'Expense deleted.');
    }
}
