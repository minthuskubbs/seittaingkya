@extends('layouts.app')
@section('title', \App\Models\ToothChargeType::KINDS[$kind])
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group">
        <a href="{{ route('tooth-charge-types.index', ['kind'=>'extraction']) }}" class="btn btn-sm {{ $kind==='extraction'?'btn-brand':'btn-outline-secondary' }}">Extraction</a>
        <a href="{{ route('tooth-charge-types.index', ['kind'=>'implant']) }}" class="btn btn-sm {{ $kind==='implant'?'btn-brand':'btn-outline-secondary' }}">Implant</a>
    </div>
    <a href="{{ route('tooth-charge-types.create', ['kind'=>$kind]) }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New {{ ucfirst($kind) }} Type</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th class="text-end">Price</th><th class="text-center">Order</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($types as $t)
        <tr>
            <td>{{ $t->name }}</td>
            <td class="text-end">{{ money($t->price) }}</td>
            <td class="text-center">{{ $t->sort_order }}</td>
            <td>{!! $t->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('tooth-charge-types.edit', $t) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('tooth-charge-types.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-4">No {{ $kind }} types yet.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $types->links() }}</div>
@endsection
