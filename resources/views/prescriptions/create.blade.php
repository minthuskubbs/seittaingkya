@extends('layouts.app')
@section('title', 'New Prescription')
@section('content')
<form method="POST" action="{{ route('prescriptions.store') }}" x-data="{ items: [{}] }">
    @csrf
    <div class="card mb-3"><div class="card-body">
        <div class="row g-3">
            @include('partials.clinic_select')
            <div class="col-md-5"><label class="form-label">Patient <span class="text-danger">*</span></label>
                <select name="patient_id" class="form-select" required><option value="">— Select —</option>
                    @foreach($patients as $p)<option value="{{ $p->id }}" @selected(old('patient_id',optional($patient??null)->id)==$p->id)>{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-select"><option value="">—</option>
                    @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Date</label>
                <input type="date" name="prescribed_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="col-12"><label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea></div>
        </div>
    </div></div>

    <div class="card"><div class="card-header d-flex justify-content-between">
        <span>Medicines</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="items.push({})"><i class="bi bi-plus"></i> Add row</button>
    </div><div class="card-body">
        <template x-for="(it, i) in items" :key="i">
            <div class="row g-2 mb-2 align-items-end border-bottom pb-2">
                <div class="col-md-3"><label class="form-label small">Medicine *</label>
                    <input :name="'items['+i+'][medicine_name]'" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Dosage</label>
                    <input :name="'items['+i+'][dosage]'" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Frequency</label>
                    <input :name="'items['+i+'][frequency]'" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Duration</label>
                    <input :name="'items['+i+'][duration]'" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Instructions</label>
                    <input :name="'items['+i+'][instructions]'" class="form-control form-control-sm"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm text-danger" @click="items.splice(i,1)" x-show="items.length>1"><i class="bi bi-x-circle"></i></button></div>
            </div>
        </template>
        <button class="btn btn-brand mt-2"><i class="bi bi-check-lg"></i> Save Prescription</button>
    </div></div>
</form>
@endsection
