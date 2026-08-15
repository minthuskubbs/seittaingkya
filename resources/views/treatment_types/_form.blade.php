@csrf
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Clinic <span class="text-danger">*</span></label>
        <select name="clinic_id" class="form-select" required>
            <option value="">— Select clinic —</option>
            @foreach(\App\Models\Clinic::where('is_active', true)->orderBy('name')->get() as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $treatmentType->clinic_id ?? session('active_clinic_id')) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select></div>
    <div class="col-md-5"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $treatmentType->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Price (MMK)</label>
        <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $treatmentType->price ?? 0) }}"></div>
    <div class="col-md-2"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $treatmentType->sort_order ?? 0) }}"></div>
    <div class="col-md-2 d-flex align-items-end"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="require_qty" id="rq" value="1" @checked(old('require_qty', $treatmentType->require_qty ?? true))>
        <label class="form-check-label" for="rq">Requires Qty</label></div></div>
    <div class="col-12"><div class="form-text">Untick "Requires Qty" for flat-rate types like Scaling (no quantity input on the treatment).</div></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="tta" value="1" @checked(old('is_active', $treatmentType->is_active ?? true))>
        <label class="form-check-label" for="tta">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Treatment Type</button>
    <a href="{{ route('treatment-types.index') }}" class="btn btn-light">Cancel</a></div>
