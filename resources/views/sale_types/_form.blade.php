@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $saleType->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Slug</label>
        <input name="slug" class="form-control" value="{{ old('slug', $saleType->slug ?? '') }}" placeholder="auto from name">
        <div class="form-text">Lowercase, letters/numbers/underscore. Leave blank to auto-generate.</div></div>
    <div class="col-md-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $saleType->sort_order ?? 0) }}"></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="sta" value="1" @checked(old('is_active', $saleType->is_active ?? true))>
        <label class="form-check-label" for="sta">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Sale Type</button>
    <a href="{{ route('sale-types.index') }}" class="btn btn-light">Cancel</a></div>
