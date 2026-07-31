@extends('layouts.app')
@section('title', 'Edit Treatment')
@section('content')
<div class="card mb-3"><div class="card-body p-4"><form method="POST" action="{{ route('treatments.update',$treatment) }}">@method('PUT')@include('treatments._form')</form></div></div>

{{-- Add medicine to this treatment. Creates a doctor-type sale linked to the
     treatment, combined into its invoice (no separate counter payment). --}}
<div class="card" x-data="txMed()"><div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-capsule"></i> Add Medicine (Doctor Sale)</span>
    <a href="{{ route('treatments.show', $treatment) }}" class="btn btn-sm btn-outline-secondary">View Invoice</a>
</div><div class="card-body">
    @if($treatment->sales->count())
        <div class="mb-3 small">
            <span class="text-muted">Already linked:</span>
            @foreach($treatment->sales as $s)<span class="badge badge-soft-brand">{{ $s->sale_no }} · {{ money($s->total) }}</span> @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('sales.store') }}">
        @csrf
        <input type="hidden" name="sale_type" value="doctor">
        <input type="hidden" name="treatment_id" value="{{ $treatment->id }}">
        <input type="hidden" name="clinic_id" value="{{ $treatment->clinic_id }}">
        <input type="hidden" name="patient_id" value="{{ $treatment->patient_id }}">
        @if($treatment->doctor_id)<input type="hidden" name="doctor_id" value="{{ $treatment->doctor_id }}">@endif
        <input type="hidden" name="payment_method" value="cash">

        <div class="input-group mb-3" style="max-width:520px">
            <select class="form-select" x-model="pick">
                <option value="">— Select medicine —</option>
                @foreach($medicines as $m)
                    <option value="{{ $m->id }}" data-name="{{ $m->name }}" data-price="{{ $m->sale_price }}">{{ $m->name }} — {{ money($m->sale_price) }} (stock {{ $m->stock_qty }})</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-brand" @click="add"><i class="bi bi-plus-lg"></i> Add</button>
        </div>

        <table class="table align-middle">
            <thead><tr><th>Item</th><th style="width:110px">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th><th></th></tr></thead>
            <tbody>
                <template x-for="(it,i) in items" :key="i">
                    <tr>
                        <td x-text="it.name"></td>
                        <td><input type="number" min="1" class="form-control form-control-sm" x-model.number="it.qty"></td>
                        <td class="text-end" x-text="fmt(it.price)"></td>
                        <td class="text-end" x-text="fmt(it.price*it.qty)"></td>
                        <td><button type="button" class="btn btn-sm text-danger" @click="items.splice(i,1)"><i class="bi bi-x-circle"></i></button></td>
                        <input type="hidden" :name="'items['+i+'][product_id]'" :value="it.id">
                        <input type="hidden" :name="'items['+i+'][quantity]'" :value="it.qty">
                    </tr>
                </template>
                <tr x-show="items.length===0"><td colspan="5" class="text-center text-muted">No medicine added.</td></tr>
            </tbody>
        </table>
        <button class="btn btn-brand" :disabled="items.length===0"><i class="bi bi-check-lg"></i> Add to Treatment Invoice</button>
    </form>
</div></div>

@push('scripts')
<script>
function txMed() {
  return {
    items: [], pick: '',
    fmt(n){ return new Intl.NumberFormat().format(n||0)+' MMK'; },
    add() {
      if (!this.pick) return;
      const opt = document.querySelector('option[value="'+this.pick+'"][data-name]');
      const ex = this.items.find(i=>i.id==this.pick);
      if (ex) ex.qty++;
      else this.items.push({ id: this.pick, name: opt.dataset.name, price: parseFloat(opt.dataset.price), qty: 1 });
      this.pick='';
    }
  }
}
</script>
@endpush
@endsection
