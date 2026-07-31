@csrf
@php $curKind = old('kind', $type->kind ?? ($kind ?? 'extraction')); @endphp
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Kind</label>
        <select name="kind" class="form-select">
            <option value="extraction" @selected($curKind==='extraction')>Tooth Extraction</option>
            <option value="implant" @selected($curKind==='implant')>Tooth Implant</option>
        </select></div>
    <div class="col-md-5"><label class="form-label">Name / Class <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $type->name ?? '') }}" placeholder="e.g. Class A" required></div>
    <div class="col-md-3"><label class="form-label">Price (MMK)</label>
        <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $type->price ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $type->sort_order ?? 0) }}"></div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="tca" value="1" @checked(old('is_active', $type->is_active ?? true))>
        <label class="form-check-label" for="tca">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save</button>
    <a href="{{ route('tooth-charge-types.index', ['kind'=>$curKind]) }}" class="btn btn-light">Cancel</a></div>
