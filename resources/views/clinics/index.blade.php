@extends('layouts.app')
@section('title', 'Clinics')
@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('clinics.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New Clinic</a></div>
<div class="row g-3">
    @foreach($clinics as $c)
        <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <h5>{{ $c->name }} <span class="badge badge-soft-brand">{{ $c->code }}</span></h5>
                <a href="{{ route('clinics.edit',$c) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            </div>
            <div class="text-muted small">{{ $c->phone ?? '' }}</div>
            <div class="text-muted small">{{ $c->address ?? '' }}</div>
            <div class="mt-2"><span class="badge bg-light text-dark">{{ $c->users_count }} users</span>
                <span class="badge bg-light text-dark">{{ $c->patients_count }} patients</span></div>
        </div></div></div>
    @endforeach
</div>
@endsection
