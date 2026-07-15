@csrf
<div class="row g-3">
    @include('partials.clinic_select', ['selectedClinicId' => $supplier->clinic_id ?? null])
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name',$supplier->name??'') }}" required></div>
    <div class="col-md-3"><label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone',$supplier->phone??'') }}"></div>
    <div class="col-md-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email',$supplier->email??'') }}"></div>
    <div class="col-12"><label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address',$supplier->address??'') }}</textarea></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="sa" value="1" @checked(old('is_active',$supplier->is_active??true))>
        <label class="form-check-label" for="sa">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Supplier</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-light">Cancel</a></div>
