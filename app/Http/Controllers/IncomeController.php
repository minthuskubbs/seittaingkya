<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct()
    {
        // Finance is restricted to users with finance.view (super admin).
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request)
    {
        $incomes = Income::query()
            ->allClinics()
            ->when($request->clinic_id, fn ($q) => $q->where('clinic_id', $request->clinic_id))
            ->latest('income_date')
            ->paginate(20)
            ->withQueryString();

        return view('incomes.index', compact('incomes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'income_date' => 'required|date',
            'note' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        Income::create($data);

        return back()->with('status', 'Income recorded.');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return back()->with('status', 'Income deleted.');
    }
}
