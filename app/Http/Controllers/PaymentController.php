<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:billing.manage')->only(['store', 'destroy']);
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

        // Guard against overpayment: a payment cannot exceed the outstanding
        // balance (rounding tolerance of 0.01), which would create a negative
        // balance and inflate the paid total.
        $balance = round($treatment->balance(), 2);
        if (round((float) $data['amount'], 2) > $balance + 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds the outstanding balance of '.money($balance).'.',
            ]);
        }

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

    /** Void a payment recorded against a treatment (e.g. a duplicate/wrong entry). */
    public function destroy(Treatment $treatment, Payment $payment)
    {
        // Only allow deleting a payment that belongs to this treatment.
        if ($payment->payable_type !== $treatment->getMorphClass() || (int) $payment->payable_id !== (int) $treatment->id) {
            abort(404);
        }

        $amount = (float) $payment->amount;
        $payment->delete();

        AuditLog::record('payment_voided', 'Voided payment '.money($amount)." for treatment #{$treatment->id}", $treatment);

        return back()->with('status', 'Payment voided.');
    }

    /** Printable invoice/voucher — combines treatment charges and any linked medicine sales. */
    public function invoice(Treatment $treatment)
    {
        $treatment->load(['patient', 'doctor', 'fees', 'payments', 'sales.items', 'clinic', 'treatmentTypes']);

        AuditLog::record('print', "Printed invoice for treatment #{$treatment->id}"
            .($treatment->patient ? " ({$treatment->patient->patient_code})" : ''), $treatment);

        return view('treatments.invoice', compact('treatment'));
    }
}
