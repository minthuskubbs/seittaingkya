@extends('layouts.app')
@section('title', 'Patient · '.$patient->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">{{ $patient->name }} <span class="badge badge-soft-brand">{{ $patient->patient_code }}</span></h4>
        <div class="text-muted small">
            Age {{ $patient->age ?? '—' }} · {{ $patient->phone ?? 'No phone' }}
            @if($patient->assignedDoctor) · {{ $patient->assignedDoctor->name }} @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @can('appointments.manage')<a href="{{ route('appointments.create') }}?patient={{ $patient->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-calendar-plus"></i> Appointment</a>@endcan
        @can('clinical.manage')<a href="{{ route('treatments.create') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clipboard2-plus"></i> Treatment</a>@endcan
        @can('patients.manage')<a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-brand"><i class="bi bi-pencil"></i> Edit</a>@endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Medical Info</div><div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-5">Diabetes</dt><dd class="col-7">{!! $patient->diabetes ? '<span class="badge bg-warning text-dark">Yes</span>' : 'No' !!}</dd>
                <dt class="col-5">Hypertension</dt><dd class="col-7">{!! $patient->hypertension ? '<span class="badge bg-warning text-dark">Yes</span>' : 'No' !!}</dd>
                <dt class="col-5">Condition</dt><dd class="col-7">{{ $patient->medical_condition ?: '—' }}</dd>
                <dt class="col-5">Drug Allergy</dt><dd class="col-7">{{ $patient->drug_allergy ?: '—' }}</dd>
                <dt class="col-5">Address</dt><dd class="col-7">{{ $patient->address ?: '—' }}</dd>
                <dt class="col-5">Doctor Desc</dt><dd class="col-7">{{ $patient->doctor_desc ?: '—' }}</dd>
                <dt class="col-5">Assistance Desc</dt><dd class="col-7">{{ $patient->assistance_desc ?: '—' }}</dd>
            </dl>
        </div></div>

        @can('clinical.manage')
        <div class="card"><div class="card-header"><i class="bi bi-paperclip"></i> Attachments</div><div class="card-body">
            <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mb-3">
                @csrf
                <input type="hidden" name="type" value="patient">
                <input type="hidden" name="id" value="{{ $patient->id }}">
                <div class="mb-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="xray">X-ray / Image</option>
                        <option value="document">Document</option>
                    </select>
                </div>
                <input type="file" name="files[]" class="form-control form-control-sm mb-2" multiple
                       accept=".png,.jpg,.jpeg,.pdf,.doc,.docx">
                <button class="btn btn-sm btn-brand w-100">Upload</button>
                <div class="form-text">PNG, JPG, JPEG, PDF, DOC, DOCX — up to 20MB each.</div>
            </form>
            <div class="row g-2">
                @forelse($patient->attachments as $att)
                    <div class="col-6">
                        <div class="border rounded p-2 text-center position-relative">
                            @if($att->isImage())
                                <a href="{{ $att->url }}" target="_blank"><img src="{{ $att->url }}" class="img-fluid rounded" style="height:70px;object-fit:cover"></a>
                            @else
                                <a href="{{ $att->url }}" target="_blank"><i class="bi bi-file-earmark-text display-6 text-muted"></i></a>
                            @endif
                            <div class="small text-truncate">{{ $att->original_name }}</div>
                            <span class="badge badge-soft-brand">{{ $att->category }}</span>
                            <form method="POST" action="{{ route('attachments.destroy', $att) }}" class="position-absolute top-0 end-0">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm text-danger p-0 px-1" onclick="return confirm('Delete?')"><i class="bi bi-x-circle-fill"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted small text-center py-2">No attachments yet.</div>
                @endforelse
            </div>
        </div></div>
        @endcan
    </div>

    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-header"><i class="bi bi-calendar-check"></i> Appointments</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>No</th><th>Date</th><th>Doctor</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($patient->appointments->sortByDesc('scheduled_at') as $a)
                    <tr>
                        <td>{{ $a->appointment_no }}</td>
                        <td>{{ $a->scheduled_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $a->doctor->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary text-capitalize">{{ $a->status }}</span></td>
                        <td class="text-end"><a href="{{ route('appointments.show', $a) }}" class="small">Open treatment →</a></td>
                    </tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-3">None</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>

        <div class="card mb-3"><div class="card-header"><i class="bi bi-clipboard2-pulse"></i> Treatment History</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Date</th><th>Patient</th><th>Treatment Type</th><th>Doctor</th><th>Appointment</th><th>Note</th><th></th></tr></thead>
                <tbody>
                @forelse($patient->treatments->sortByDesc('treatment_date') as $t)
                    <tr>
                        <td>{{ $t->treatment_date?->format('Y-m-d') }}</td>
                        <td class="small">{{ $patient->patient_code }} — {{ $patient->name }}</td>
                        <td>@forelse($t->treatmentTypes as $tt)<span class="badge badge-soft-brand">{{ $tt->name }}</span> @empty<span class="text-muted">—</span>@endforelse</td>
                        <td>{{ $t->doctor->name ?? '—' }}</td>
                        <td>{{ $t->appointment?->appointment_no ?? '—' }}</td>
                        <td class="small">{{ Str::limit($t->notes, 50) ?: '—' }}</td>
                        <td class="text-end"><a href="{{ route('treatments.show', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty<tr><td colspan="7" class="text-center text-muted py-3">None</td></tr>@endforelse
                </tbody>
            </table></div>
        </div>

        <div class="card mb-3"><div class="card-header"><i class="bi bi-chat-left-text"></i> Doctor Feedback</div>
            <div class="card-body">
                @can('doctor_notes.manage')
                <form method="POST" action="{{ route('patients.feedback.store', $patient) }}" class="mb-3">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5"><select name="doctor_id" class="form-select form-select-sm">
                            <option value="">— Doctor —</option>
                            @foreach($doctors as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                        </select></div>
                        <div class="col-12"><textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Current doctor's feedback…" required></textarea></div>
                        <div class="col-12"><button class="btn btn-sm btn-brand">Add Feedback</button></div>
                    </div>
                </form>
                @endcan
                @forelse($patient->feedbacks as $fb)
                    <div class="border-bottom pb-2 mb-2" x-data="{ edit: false }">
                        <div class="d-flex justify-content-between">
                            <div class="small text-muted">
                                {{ $fb->created_at->format('Y-m-d H:i') }}
                                @if($fb->doctor) · {{ $fb->doctor->name }} @endif
                                · by {{ $fb->author->name ?? '—' }}
                            </div>
                            @role('super_admin')<a href="#" class="small" @click.prevent="edit=!edit">edit</a>@endrole
                        </div>
                        <div x-show="!edit" style="white-space:pre-line">{{ $fb->note }}</div>
                        @role('super_admin')
                        <form x-show="edit" method="POST" action="{{ route('feedback.update', $fb) }}" class="mt-1" style="display:none">
                            @csrf @method('PUT')
                            <textarea name="note" class="form-control form-control-sm mb-1" rows="2">{{ $fb->note }}</textarea>
                            <button class="btn btn-sm btn-brand">Save</button>
                        </form>
                        @endrole
                    </div>
                @empty<div class="text-muted small text-center">No feedback yet.</div>@endforelse
            </div>
        </div>

        <div class="card"><div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-capsule"></i> Prescriptions</span>
            @can('clinical.manage')<a href="{{ route('prescriptions.create') }}?patient_id={{ $patient->id }}" class="small">+ Add</a>@endcan
        </div>
            <ul class="list-group list-group-flush">
                @forelse($patient->prescriptions->sortByDesc('prescribed_date') as $pr)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('prescriptions.show', $pr) }}">{{ $pr->prescribed_date?->format('Y-m-d') }}</a>
                        <span class="text-muted small">{{ $pr->doctor->name ?? '' }}</span>
                    </li>
                @empty<li class="list-group-item text-muted text-center">None</li>@endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
