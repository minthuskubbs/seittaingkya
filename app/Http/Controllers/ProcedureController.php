<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:procedures.manage');
    }

    public function index()
    {
        $procedures = Procedure::orderBy('name')->paginate(20);

        return view('procedures.index', compact('procedures'));
    }

    public function create()
    {
        return view('procedures.create');
    }

    public function store(Request $request)
    {
        Procedure::create($this->validated($request));

        return redirect()->route('procedures.index')->with('status', 'Procedure created.');
    }

    public function edit(Procedure $procedure)
    {
        return view('procedures.edit', compact('procedure'));
    }

    public function update(Request $request, Procedure $procedure)
    {
        $procedure->update($this->validated($request));

        return redirect()->route('procedures.index')->with('status', 'Procedure updated.');
    }

    public function destroy(Procedure $procedure)
    {
        $procedure->delete();

        return redirect()->route('procedures.index')->with('status', 'Procedure deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'default_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
