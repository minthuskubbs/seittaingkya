<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function __construct()
    {
        // Only the super admin creates / edits / deletes the fee catalogue.
        $this->middleware('permission:fees.manage');
    }

    public function index(Request $request)
    {
        // Super admin manages every clinic's fees; default to the working clinic.
        $isSuper = ! auth()->user()->clinic_id;
        $clinicFilter = $request->input('clinic_id', $isSuper ? session('active_clinic_id') : null);

        $fees = Fee::with('clinic')
            ->when($clinicFilter, fn ($q) => $q->where('clinic_id', $clinicFilter))
            ->orderBy('category')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $clinics = $isSuper ? \App\Models\Clinic::orderBy('name')->get() : collect();

        return view('fees.index', compact('fees', 'clinics', 'clinicFilter'));
    }

    public function create()
    {
        return view('fees.create');
    }

    public function store(Request $request)
    {
        $fee = Fee::create($this->validated($request));
        AuditLog::record('created', "Created fee {$fee->name}", $fee);

        return redirect()->route('fees.index')->with('status', 'Fee created.');
    }

    public function edit(Fee $fee)
    {
        return view('fees.edit', compact('fee'));
    }

    public function update(Request $request, Fee $fee)
    {
        // NOTE: updating a fee only changes future appointments. Historical
        // appointment_fee rows already hold snapshotted prices and are untouched.
        $fee->update($this->validated($request));
        AuditLog::record('updated', "Updated fee {$fee->name}", $fee);

        return redirect()->route('fees.index')->with('status', 'Fee updated. New prices apply to future appointments only.');
    }

    public function destroy(Fee $fee)
    {
        AuditLog::record('deleted', "Deleted fee {$fee->name}", $fee);
        $fee->delete();

        return redirect()->route('fees.index')->with('status', 'Fee deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            // Fees belong to a clinic; the super admin picks which one.
            'clinic_id' => 'required|exists:clinics,id',
            'name' => 'required|string|max:255',
            'category' => 'required|in:service,xray,scanner,dentist,other',
            'fee_group' => 'required|in:treatment,service',
            'price' => 'required|numeric|min:0',
            'is_foc' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_foc'] = $request->boolean('is_foc');
        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['is_foc']) {
            $data['price'] = 0;
        }

        return $data;
    }
}
