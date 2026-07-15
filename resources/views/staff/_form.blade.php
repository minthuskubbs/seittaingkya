@csrf
<div class="row g-3">
    @include('partials.clinic_select', ['selectedClinicId' => $staff->clinic_id ?? null])
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $staff->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Position</label>
        <input name="position" class="form-control" value="{{ old('position', $staff->position ?? '') }}" placeholder="e.g. Receptionist"></div>
    <div class="col-md-3"><label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone', $staff->phone ?? '') }}"></div>

    <div class="col-12"><hr class="my-1"><div class="text-muted small">Default monthly pay — payroll pre-fills from these (adjustable per month). Bonus is entered in payroll.</div></div>
    <div class="col-md-4"><label class="form-label">Basic Salary (MMK)</label>
        <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" value="{{ old('basic_salary', $staff->basic_salary ?? 0) }}"></div>
    <div class="col-md-4"><label class="form-label">Attendance Allowance (MMK)</label>
        <input type="number" step="0.01" min="0" name="attendance_allowance" class="form-control" value="{{ old('attendance_allowance', $staff->attendance_allowance ?? 0) }}"></div>
    <div class="col-md-4"><label class="form-label">Transportation Allowance (MMK)</label>
        <input type="number" step="0.01" min="0" name="transportation_allowance" class="form-control" value="{{ old('transportation_allowance', $staff->transportation_allowance ?? 0) }}"></div>

    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="sfa" value="1" @checked(old('is_active', $staff->is_active ?? true))>
        <label class="form-check-label" for="sfa">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Staff</button>
    <a href="{{ route('staff.index') }}" class="btn btn-light">Cancel</a></div>
