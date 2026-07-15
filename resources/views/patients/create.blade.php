@extends('layouts.app')
@section('title', 'New Patient')

@section('content')
<div class="card"><div class="card-body p-4">
    {{-- data-offline enables saving this form to IndexedDB when there's no connection --}}
    <form method="POST" action="{{ route('patients.store') }}"
          data-offline="patient" data-offline-action="create" data-offline-redirect="{{ route('patients.index') }}">
        @include('patients._form')
    </form>
</div></div>
@endsection
