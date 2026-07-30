@extends('layouts.app')
@section('title', 'New Sale (POS)')
@section('content')
<form method="POST" action="{{ route('sales.store') }}" x-data="pos()" @submit="prepare">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card"><div class="card-header">Add Products</div><div class="card-body">
                <div class="input-group mb-3">
                    <select class="form-select" x-model="pick">
                        <option value="">— Select product —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->sale_price }}" data-stock="{{ $p->stock_qty }}">
                                {{ $p->name }} — {{ money($p->sale_price) }} (stock {{ $p->stock_qty }})</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-brand" @click="add"><i class="bi bi-plus-lg"></i> Add</button>
                </div>
                <table class="table align-middle">
                    <thead><tr><th>Item</th><th style="width:120px">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="(it, i) in items" :key="i">
                            <tr>
                                <td x-text="it.name"></td>
                                <td><input type="number" min="1" class="form-control form-control-sm" x-model.number="it.qty" @input="recalc"></td>
                                <td class="text-end" x-text="fmt(it.price)"></td>
                                <td class="text-end" x-text="fmt(it.price*it.qty)"></td>
                                <td><button type="button" class="btn btn-sm text-danger" @click="remove(i)"><i class="bi bi-x-circle"></i></button></td>
                            </tr>
                        </template>
                        <tr x-show="items.length===0"><td colspan="5" class="text-center text-muted">No items.</td></tr>
                    </tbody>
                </table>
                <template x-for="(it,i) in items" :key="'h'+i">
                    <span>
                        <input type="hidden" :name="'items['+i+'][product_id]'" :value="it.id">
                        <input type="hidden" :name="'items['+i+'][quantity]'" :value="it.qty">
                    </span>
                </template>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card"><div class="card-header">Sale Details</div><div class="card-body">
                @unless(auth()->user()->clinic_id)
                    <div class="mb-2"><label class="form-label">Clinic <span class="text-danger">*</span></label>
                        <select name="clinic_id" class="form-select" required>
                            <option value="">— Select clinic —</option>
                            @foreach(\App\Models\Clinic::where('is_active',true)->orderBy('name')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select></div>
                @endunless
                {{-- This page is walk-in medicine sales only. Treatment/doctor
                     medicine sales are added from the treatment page. --}}
                <input type="hidden" name="sale_type" value="walk_in">
                <div class="mb-2"><label class="form-label">Customer Name (walk-in)</label>
                    <input name="customer_name" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Discount</label>
                    <input type="number" step="0.01" name="discount" class="form-control" x-model.number="discount" @input="recalc" value="0"></div>
                <div class="mb-3"><label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        @foreach(\App\Models\Payment::METHODS as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select></div>
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong x-text="fmt(subtotal)"></strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><span x-text="fmt(discount)"></span></div>
                <div class="d-flex justify-content-between h5 mt-1"><span>Total</span><span class="text-brand" x-text="fmt(total)"></span></div>
                <button class="btn btn-brand w-100 mt-3" :disabled="items.length===0"><i class="bi bi-check-lg"></i> Complete Sale</button>
            </div></div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function pos() {
  return {
    items: [], pick: '', discount: 0, saleType: 'walk_in', subtotal: 0, total: 0,
    fmt(n){ return new Intl.NumberFormat().format(n||0)+' MMK'; },
    add() {
      if (!this.pick) return;
      const opt = document.querySelector('option[value="'+this.pick+'"]');
      const existing = this.items.find(i=>i.id==this.pick);
      if (existing) { existing.qty++; }
      else this.items.push({ id: this.pick, name: opt.dataset.name, price: parseFloat(opt.dataset.price), qty: 1 });
      this.pick=''; this.recalc();
    },
    remove(i){ this.items.splice(i,1); this.recalc(); },
    recalc(){ this.subtotal = this.items.reduce((s,i)=>s+i.price*i.qty,0); this.total = Math.max(0,this.subtotal-(this.discount||0)); },
    prepare(){ /* hidden inputs bound via x-for */ }
  }
}
</script>
@endpush
@endsection
