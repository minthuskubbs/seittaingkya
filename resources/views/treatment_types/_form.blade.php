@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $treatmentType->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $treatmentType->sort_order ?? 0) }}"></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="tta" value="1" @checked(old('is_active', $treatmentType->is_active ?? true))>
        <label class="form-check-label" for="tta">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Treatment Type</button>
    <a href="{{ route('treatment-types.index') }}" class="btn btn-light">Cancel</a></div>
