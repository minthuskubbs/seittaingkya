{{-- Shown only to super admins (who have no fixed clinic). Clinic-bound users
     inherit their own clinic automatically, so the field is hidden for them. --}}
@unless(auth()->user()->clinic_id)
    <div class="col-md-6">
        <label class="form-label">Clinic <span class="text-danger">*</span></label>
        <select name="clinic_id" class="form-select" required>
            <option value="">— Select clinic —</option>
            @foreach(\App\Models\Clinic::where('is_active', true)->orderBy('name')->get() as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $selectedClinicId ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
@endunless
