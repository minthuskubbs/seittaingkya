@csrf
@php
    $selectedFees = old('fees', isset($treatment) ? $treatment->fees->pluck('fee_id')->all() : []);
@endphp
<div x-data="txBilling()">
<div class="row g-3">
    @unless(isset($treatment))
        @include('partials.clinic_select', ['selectedClinicId' => null])
    @endunless
    <div class="col-md-6"><label class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" class="form-select" required>
            <option value="">— Select by code / name —</option>
            @foreach($patients as $p)
                <option value="{{ $p->id }}" @selected(old('patient_id', $treatment->patient_id ?? optional($patient ?? null)->id)==$p->id)>{{ $p->patient_code }} — {{ $p->name }}</option>
            @endforeach
        </select></div>
    <div class="col-md-3"><label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select"><option value="">—</option>
            @foreach($doctors as $d)<option value="{{ $d->id }}" @selected(old('doctor_id',$treatment->doctor_id??'')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="treatment_date" class="form-control" value="{{ old('treatment_date', isset($treatment)?$treatment->treatment_date?->format('Y-m-d'):date('Y-m-d')) }}" required></div>

    <div class="col-md-3"><label class="form-label">Tooth</label>
        <input name="tooth" class="form-control" value="{{ old('tooth',$treatment->tooth??'') }}"></div>
    <div class="col-md-6"><label class="form-label">Diagnosis</label>
        <input name="diagnosis" class="form-control" value="{{ old('diagnosis',$treatment->diagnosis??'') }}"></div>

    @php
        $selectedTypes = old('treatment_types', isset($treatment) ? $treatment->treatmentTypes->pluck('id')->all() : []);
        $typeQty = old('treatment_type_qty', isset($treatment) ? $treatment->treatmentTypes->pluck('pivot.qty', 'id')->all() : []);
    @endphp
    <div class="col-12">
        <label class="form-label">Treatment Type(s)</label>
        <div class="row g-2">
            @forelse($treatmentTypes as $tt)
                <div class="col-md-4 col-6">
                    <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                        <input type="checkbox" class="form-check-input mt-0 tt-check" name="treatment_types[]" value="{{ $tt->id }}"
                               data-price="{{ $tt->price }}" data-qty="{{ $tt->require_qty ? 1 : 0 }}" @checked(in_array($tt->id, $selectedTypes)) @change="recalc()">
                        <span class="small flex-grow-1">{{ $tt->name }}<br><span class="text-muted">{{ money($tt->price) }}</span></span>
                        @if($tt->require_qty)
                            <input type="number" min="1" name="treatment_type_qty[{{ $tt->id }}]" class="form-control form-control-sm" style="width:64px"
                                   value="{{ $typeQty[$tt->id] ?? 1 }}" @input="recalc()" title="Qty">
                        @endif
                    </label>
                </div>
            @empty
                <div class="col-12 text-muted small">No treatment types yet — add them under Administration → Treatment Types.</div>
            @endforelse
        </div>
    </div>
    <div class="col-12"><label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes',$treatment->notes??'') }}</textarea></div>
</div>

<hr class="my-4">
<h6 class="fw-bold mb-3"><i class="bi bi-cash-coin"></i> Charges</h6>

<div class="row g-3">
    <div class="col-lg-7">
        @php $feeGroups = $fees->groupBy('fee_group'); @endphp
        @foreach(\App\Models\Fee::GROUPS as $gKey => $gLabel)
            <label class="form-label d-block mt-2">{{ $gLabel }}</label>
            <div class="row g-2">
                @forelse($feeGroups->get($gKey, collect()) as $fee)
                    <div class="col-md-6">
                        <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                            <input type="checkbox" class="form-check-input mt-0" name="fees[]" value="{{ $fee->id }}"
                                   data-price="{{ $fee->effectivePrice() }}" @checked(in_array($fee->id, $selectedFees)) @change="recalc()">
                            <span class="small">{{ $fee->name }}<br>
                                <span class="text-muted">{{ $fee->is_foc ? 'FOC (0)' : money($fee->effectivePrice()) }}</span></span>
                        </label>
                    </div>
                @empty
                    <div class="col-12 text-muted small">No {{ strtolower($gLabel) }} configured.</div>
                @endforelse
            </div>
        @endforeach
        <div class="form-text mt-1">Only Treatment Fees earn doctor commission; Services Fees do not.</div>
    </div>

    <div class="col-lg-5">
        <div class="row g-2">
            <div class="col-12"><label class="form-label mb-1">Tooth Extraction (type × qty)</label>
                <div class="input-group input-group-sm">
                    <select name="extraction_type_id" class="form-select" @change="exPrice = +($event.target.selectedOptions[0].dataset.price||0); recalc()">
                        <option value="" data-price="0">— None —</option>
                        @foreach($extractionTypes as $et)
                            <option value="{{ $et->id }}" data-price="{{ $et->price }}" @selected(old('extraction_type_id',$treatment->extraction_type_id??'')==$et->id)>{{ $et->name }} ({{ money($et->price) }})</option>
                        @endforeach
                    </select>
                    <span class="input-group-text">Qty</span>
                    <input type="number" min="0" name="extraction_qty" class="form-control" x-model.number="exQty" @input="recalc()" value="{{ old('extraction_qty',$treatment->extraction_qty??0) }}">
                    <span class="input-group-text" x-text="fmt(exPrice*exQty)"></span>
                </div>
            </div>
            <div class="col-12"><label class="form-label mb-1">Tooth Implant (type × qty)</label>
                <div class="input-group input-group-sm">
                    <select name="implant_type_id" class="form-select" @change="imPrice = +($event.target.selectedOptions[0].dataset.price||0); recalc()">
                        <option value="" data-price="0">— None —</option>
                        @foreach($implantTypes as $it)
                            <option value="{{ $it->id }}" data-price="{{ $it->price }}" @selected(old('implant_type_id',$treatment->implant_type_id??'')==$it->id)>{{ $it->name }} ({{ money($it->price) }})</option>
                        @endforeach
                    </select>
                    <span class="input-group-text">Qty</span>
                    <input type="number" min="0" name="implant_qty" class="form-control" x-model.number="imQty" @input="recalc()" value="{{ old('implant_qty',$treatment->implant_qty??0) }}">
                    <span class="input-group-text" x-text="fmt(imPrice*imQty)"></span>
                </div>
            </div>
            <div class="col-4"><label class="form-label mb-1">Surgery</label>
                <input type="number" min="0" step="0.01" name="surgery_charge" class="form-control form-control-sm" x-model.number="surgery" @input="recalc()" value="{{ old('surgery_charge',$treatment->surgery_charge??0) }}"></div>
            <div class="col-4"><label class="form-label mb-1">Denture</label>
                <input type="number" min="0" step="0.01" name="denture_charge" class="form-control form-control-sm" x-model.number="denture" @input="recalc()" value="{{ old('denture_charge',$treatment->denture_charge??0) }}"></div>
            <div class="col-4"><label class="form-label mb-1">Additional</label>
                <input type="number" min="0" step="0.01" name="additional_charge" class="form-control form-control-sm" x-model.number="additional" @input="recalc()" value="{{ old('additional_charge',$treatment->additional_charge??0) }}"></div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-6"><label class="form-label mb-1">Discount Type</label>
                <select name="discount_type" class="form-select form-select-sm" x-model="discountType" @change="recalc()">
                    <option value="fixed" @selected(old('discount_type',$treatment->discount_type??'fixed')==='fixed')>Fixed (MMK)</option>
                    <option value="percent" @selected(old('discount_type',$treatment->discount_type??'')==='percent')>Percent (%)</option>
                </select></div>
            <div class="col-6"><label class="form-label mb-1">Discount Value</label>
                <input type="number" min="0" step="0.01" name="discount_value" class="form-control form-control-sm" x-model.number="discountValue" @input="recalc()" value="{{ old('discount_value',$treatment->discount_value??0) }}"></div>
        </div>
        <div class="card mt-3"><div class="card-body py-2">
            <div class="d-flex justify-content-between mb-1"><span>Charges Total</span><span x-text="fmt(total)"></span></div>
            <div class="d-flex justify-content-between mb-1 text-danger"><span>Discount</span><span x-text="'- ' + fmt(discountAmount)"></span></div>
            <div class="d-flex justify-content-between h5 mb-0">Net Total <span class="text-brand" x-text="fmt(net)"></span></div>
            <div class="form-text">Linked medicine sales are added on top in the treatment invoice.</div>
        </div></div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Save Treatment</button>
    @isset($treatment)<a href="{{ route('treatments.show', $treatment) }}" class="btn btn-outline-secondary">View / Invoice</a>@endisset
    <a href="{{ route('treatments.index') }}" class="btn btn-light">Cancel</a>
</div>
</div>

@push('scripts')
<script>
function txBilling() {
  return {
    exPrice: {{ old('extraction_price',$treatment->extraction_price??0) }}, exQty: {{ old('extraction_qty',$treatment->extraction_qty??0) }},
    imPrice: {{ old('implant_price',$treatment->implant_price??0) }}, imQty: {{ old('implant_qty',$treatment->implant_qty??0) }},
    surgery: {{ old('surgery_charge',$treatment->surgery_charge??0) }}, denture: {{ old('denture_charge',$treatment->denture_charge??0) }},
    additional: {{ old('additional_charge',$treatment->additional_charge??0) }}, total: 0,
    discountType: '{{ old('discount_type',$treatment->discount_type??'fixed') }}',
    discountValue: {{ old('discount_value',$treatment->discount_value??0) }},
    discountAmount: 0, net: 0,
    init() { this.recalc(); },
    fmt(n) { return new Intl.NumberFormat().format(Math.round(n||0)) + ' MMK'; },
    recalc() {
      let fees = 0;
      document.querySelectorAll('input[name="fees[]"]:checked').forEach(el => fees += parseFloat(el.dataset.price||0));
      // Treatment types: price × qty (flat when the type has no qty).
      let types = 0;
      document.querySelectorAll('input.tt-check:checked').forEach(el => {
        const price = parseFloat(el.dataset.price||0);
        let qty = 1;
        if (el.dataset.qty === '1') {
          const q = el.closest('label').querySelector('input[type="number"]');
          qty = q ? Math.max(1, parseInt(q.value)||1) : 1;
        }
        types += price * qty;
      });
      this.total = fees + types + (this.exPrice*this.exQty) + (this.imPrice*this.imQty)
        + (this.surgery||0) + (this.denture||0) + (this.additional||0);
      const v = this.discountValue || 0;
      this.discountAmount = this.discountType === 'percent' ? this.total * Math.min(v,100) / 100 : Math.min(v, this.total);
      this.net = Math.max(0, this.total - this.discountAmount);
    }
  }
}
</script>
@endpush
