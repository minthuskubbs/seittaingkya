@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name',$procedure->name??'') }}" required></div>
    <div class="col-md-3"><label class="form-label">Code</label>
        <input name="code" class="form-control" value="{{ old('code',$procedure->code??'') }}"></div>
    <div class="col-md-3"><label class="form-label">Default Price</label>
        <input type="number" step="0.01" name="default_price" class="form-control" value="{{ old('default_price',$procedure->default_price??0) }}"></div>
    <div class="col-12"><label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description',$procedure->description??'') }}</textarea></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="pca" value="1" @checked(old('is_active',$procedure->is_active??true))>
        <label class="form-check-label" for="pca">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Procedure</button>
    <a href="{{ route('procedures.index') }}" class="btn btn-light">Cancel</a></div>
