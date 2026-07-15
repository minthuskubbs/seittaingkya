<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:staff.manage');
    }

    public function index(Request $request)
    {
        $staff = Staff::with('clinic')
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        Staff::create($this->validated($request));

        return redirect()->route('staff.index')->with('status', 'Staff created.');
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $staff->update($this->validated($request));

        return redirect()->route('staff.index')->with('status', 'Staff updated.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')->with('status', 'Staff deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'clinic_id' => 'nullable|exists:clinics,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'basic_salary' => 'nullable|numeric|min:0',
            'attendance_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
        ]);
        $data['basic_salary'] = $data['basic_salary'] ?? 0;
        $data['attendance_allowance'] = $data['attendance_allowance'] ?? 0;
        $data['transportation_allowance'] = $data['transportation_allowance'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        // clinic_id auto-filled for clinic users; super admin supplies it.
        if (auth()->user()->clinic_id) {
            unset($data['clinic_id']);
        }

        return $data;
    }
}
