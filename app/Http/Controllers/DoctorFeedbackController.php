<?php

namespace App\Http\Controllers;

use App\Models\DoctorFeedback;
use App\Models\Patient;
use Illuminate\Http\Request;

class DoctorFeedbackController extends Controller
{
    /** Adding feedback needs doctor_notes.manage; editing is super admin only. */
    public function store(Request $request, Patient $patient)
    {
        abort_unless(auth()->user()->can('doctor_notes.manage'), 403);

        $data = $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'treatment_id' => 'nullable|exists:treatments,id',
            'note' => 'required|string',
        ]);

        $patient->feedbacks()->create([
            'clinic_id' => $patient->clinic_id,
            'doctor_id' => $data['doctor_id'] ?? null,
            'treatment_id' => $data['treatment_id'] ?? null,
            'note' => $data['note'],
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Doctor feedback added.');
    }

    public function update(Request $request, DoctorFeedback $feedback)
    {
        // Previous feedback can only be edited by the super admin.
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $feedback->update($request->validate(['note' => 'required|string']));

        return back()->with('status', 'Feedback updated.');
    }
}
