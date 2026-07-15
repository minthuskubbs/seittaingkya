<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Treatment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:billing.manage')->only('store');
        $this->middleware('permission:billing.view')->only('invoice');
    }

    /** Record a payment against a treatment (patient billing). */
    public function store(Request $request, Treatment $treatment)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,kbzpay,wavepay,card,bank',
            'reference' => 'nullable|string|max:255',
            'paid_at' => 'required|date',
        ]);

        $treatment->payments()->create([
            'clinic_id' => $treatment->clinic_id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => $data['paid_at'],
            'created_by' => auth()->id(),
        ]);

        AuditLog::record('payment', 'Payment '.money($data['amount'])." for treatment #{$treatment->id}", $treatment);

        return back()->with('status', 'Payment recorded.');
    }

    /** Printable invoice/voucher — combines treatment charges and any linked medicine sales. */
    public function invoice(Treatment $treatment)
    {
        $treatment->load(['patient', 'doctor', 'fees', 'payments', 'sales.items', 'clinic']);

        AuditLog::record('print', "Printed invoice for treatment #{$treatment->id}"
            .($treatment->patient ? " ({$treatment->patient->patient_code})" : ''), $treatment);

        return view('treatments.invoice', compact('treatment'));
    }
}
