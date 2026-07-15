@csrf
@php
    $isSuper = ! auth()->user()->clinic_id;
    $feeClinic = auth()->user()->clinic_id
        ?: old('clinic_id', $appointment->clinic_id ?? session('active_clinic_id'));
@endphp
<div class="row g-3" x-data="apptForm({{ (int) $feeClinic }})">
    @if($isSuper)
        <div class="col-md-6">
            <label class="form-label">Clinic <span class="text-danger">*</span></label>
            <select name="clinic_id" class="form-select" required x-model.number="clinic" @change="filterPeople()">
                <option value="">— Select clinic —</option>
                @foreach(\App\Models\Clinic::where('is_active', true)->orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}" @selected((int) $feeClinic === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-md-6">
        <label class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach($patients as $p)
                <option value="{{ $p->id }}" data-clinic="{{ $p->clinic_id }}" @selected(old('patient_id', $appointment->patient_id ?? request('patient')) == $p->id)>{{ $p->name }} ({{ $p->patient_code }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select">
            <option value="">— None —</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" data-clinic="{{ $d->clinic_id }}" @selected(old('doctor_id', $appointment->doctor_id ?? '') == $d->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Date & Time <span class="text-danger">*</span></label>
        <input type="datetime-local" name="scheduled_at" class="form-control" required
               value="{{ old('scheduled_at', isset($appointment) ? $appointment->scheduled_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['booked','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected(old('status', $appointment->status ?? 'booked')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Reminder At</label>
        <input type="datetime-local" name="reminder_at" class="form-control"
               value="{{ old('reminder_at', isset($appointment) && $appointment->reminder_at ? $appointment->reminder_at->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Reason</label>
        <input name="reason" class="form-control" value="{{ old('reason', $appointment->reason ?? '') }}">
    </div>
    @isset($parent)
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
        <div class="col-12"><div class="alert alert-info py-2 mb-0">Follow-up of {{ $parent->appointment_no }}</div></div>
    @endisset

    @unless(isset($appointment))
        <div class="col-12"><div class="alert alert-light py-2 mb-0 small">
            <i class="bi bi-info-circle"></i> A treatment is created automatically — you'll add the charges (fees, per-tooth, etc.) on the treatment screen next.
        </div></div>
    @endunless
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Save Appointment</button>
    <a href="{{ route('appointments.index') }}" class="btn btn-light">Cancel</a>
</div>

@push('scripts')
<script>
function apptForm(initialClinic) {
  return {
    clinic: initialClinic || '',
    init() { this.filterPeople(); },
    filterPeople() {
      ['patient_id', 'doctor_id'].forEach(field => {
        const sel = document.querySelector('select[name="' + field + '"]');
        if (!sel) return;
        sel.querySelectorAll('option').forEach(opt => {
          if (!opt.value) return;
          const match = String(opt.dataset.clinic) === String(this.clinic);
          opt.hidden = !match; opt.disabled = !match;
        });
        if (sel.value && sel.selectedOptions[0] && sel.selectedOptions[0].disabled) sel.value = '';
      });
    }
  }
}
</script>
@endpush
