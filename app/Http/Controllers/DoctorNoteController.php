<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DoctorNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:doctor_notes.manage');
    }

    /** A doctor adds / updates their note on an appointment. */
    public function update(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'doctor_note' => 'nullable|string',
        ]);

        $appointment->update($data);
        AuditLog::record('doctor_note', "Doctor note updated on {$appointment->appointment_no}", $appointment);

        return back()->with('status', 'Doctor note saved.');
    }
}
