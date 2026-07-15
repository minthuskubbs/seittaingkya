@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="row g-3">
    @php $cards = [
        ['reports.patients','people','Patient Report','All patients with visit counts'],
        ['reports.treatments','clipboard2-pulse','Treatment Report','Treatments by date range'],
        ['reports.treatment_list','list-ol','Treatment List','By Tx-name: patients & income'],
        ['reports.sales','cart3','Sales Report','Daily medicine sales'],
        ['reports.inventory','box-seam','Inventory Report','Stock levels & value'],
    ]; @endphp
    @foreach($cards as [$route,$icon,$title,$desc])
        <div class="col-md-6 col-lg-4">
            <a href="{{ route($route) }}" class="card h-100 text-decoration-none"><div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon bg-brand"><i class="bi bi-{{ $icon }}"></i></div>
                    <div><div class="fw-semibold text-dark">{{ $title }}</div><div class="text-muted small">{{ $desc }}</div></div>
                </div>
            </div></a>
        </div>
    @endforeach
    @can('finance.view')
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reports.financial') }}" class="card h-100 text-decoration-none"><div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon bg-brand-2"><i class="bi bi-cash-stack"></i></div>
                    <div><div class="fw-semibold text-dark">Financial Report</div><div class="text-muted small">Revenue summary</div></div>
                </div>
            </div></a>
        </div>
    @endcan
</div>
@endsection
