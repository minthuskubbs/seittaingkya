@csrf
<div class="row g-3">
    @include('partials.clinic_select', ['selectedClinicId' => $doctor->clinic_id ?? null])
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $doctor->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone', $doctor->phone ?? '') }}"></div>
    <div class="col-md-3"><label class="form-label">Specialization</label>
        <input name="specialization" class="form-control" value="{{ old('specialization', $doctor->specialization ?? '') }}"></div>

    <div class="col-md-4"><label class="form-label">One-Day Salary (MMK) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="one_day_salary" class="form-control" value="{{ old('one_day_salary', $doctor->one_day_salary ?? 0) }}" required>
        <div class="form-text">Basic salary paid per working day.</div></div>
    <div class="col-md-4"><label class="form-label">Commission (%) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" max="100" name="commission_percent" class="form-control" value="{{ old('commission_percent', $doctor->commission_percent ?? 0) }}" required>
        <div class="form-text">Applied to the monthly commission base.</div></div>
    <div class="col-md-4 d-flex align-items-end"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="da" value="1" @checked(old('is_active', $doctor->is_active ?? true))>
        <label class="form-check-label" for="da">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Doctor</button>
    <a href="{{ route('doctors.index') }}" class="btn btn-light">Cancel</a></div>
