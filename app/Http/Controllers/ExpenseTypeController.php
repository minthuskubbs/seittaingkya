<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    public function index()
    {
        $expenseTypes = ExpenseType::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('expense_types.index', compact('expenseTypes'));
    }

    public function create()
    {
        return view('expense_types.create');
    }

    public function store(Request $request)
    {
        ExpenseType::create($this->validated($request));

        return redirect()->route('expense-types.index')->with('status', 'Expense type created.');
    }

    public function edit(ExpenseType $expenseType)
    {
        return view('expense_types.edit', compact('expenseType'));
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $expenseType->update($this->validated($request));

        return redirect()->route('expense-types.index')->with('status', 'Expense type updated.');
    }

    public function destroy(ExpenseType $expenseType)
    {
        if (\App\Models\Expense::where('expense_type_id', $expenseType->id)->exists()) {
            return back()->withErrors(['delete' => 'This type is used by existing expenses. Deactivate it instead.']);
        }
        $expenseType->delete();

        return redirect()->route('expense-types.index')->with('status', 'Expense type deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
