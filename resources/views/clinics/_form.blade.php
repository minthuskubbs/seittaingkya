@csrf
<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name',$clinic->name??'') }}" required></div>
    <div class="col-md-4"><label class="form-label">Code <span class="text-danger">*</span></label>
        <input name="code" class="form-control" value="{{ old('code',$clinic->code??'') }}" required></div>
    <div class="col-md-6"><label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone',$clinic->phone??'') }}"></div>
    <div class="col-12"><label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address',$clinic->address??'') }}</textarea></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="ca" value="1" @checked(old('is_active',$clinic->is_active??true))>
        <label class="form-check-label" for="ca">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Clinic</button>
    <a href="{{ route('clinics.index') }}" class="btn btn-light">Cancel</a></div>
