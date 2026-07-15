@csrf
<div class="row g-3">
    @include('partials.clinic_select', ['selectedClinicId' => $patient->clinic_id ?? null])
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $patient->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Age</label>
        <input type="number" name="age" class="form-control" value="{{ old('age', $patient->age ?? '') }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone', $patient->phone ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $patient->address ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Assigned Doctor</label>
        <select name="assigned_doctor_id" class="form-select">
            <option value="">— None —</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(old('assigned_doctor_id', $patient->assigned_doctor_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 d-flex align-items-end gap-4">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="diabetes" id="diabetes" value="1" @checked(old('diabetes', $patient->diabetes ?? false))>
            <label class="form-check-label" for="diabetes">Diabetes</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="hypertension" id="hypertension" value="1" @checked(old('hypertension', $patient->hypertension ?? false))>
            <label class="form-check-label" for="hypertension">Hypertension</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Medical Condition</label>
        <textarea name="medical_condition" class="form-control" rows="2">{{ old('medical_condition', $patient->medical_condition ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Drug Allergy</label>
        <textarea name="drug_allergy" class="form-control" rows="2">{{ old('drug_allergy', $patient->drug_allergy ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Doctor Description</label>
        <textarea name="doctor_desc" class="form-control" rows="2">{{ old('doctor_desc', $patient->doctor_desc ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Assistance Description</label>
        <textarea name="assistance_desc" class="form-control" rows="2">{{ old('assistance_desc', $patient->assistance_desc ?? '') }}</textarea>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Save Patient</button>
    <a href="{{ route('patients.index') }}" class="btn btn-light">Cancel</a>
</div>
