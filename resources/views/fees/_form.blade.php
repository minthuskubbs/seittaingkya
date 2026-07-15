@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Clinic <span class="text-danger">*</span></label>
        <select name="clinic_id" class="form-select" required>
            <option value="">— Select clinic —</option>
            @foreach(\App\Models\Clinic::where('is_active', true)->orderBy('name')->get() as $c)
                <option value="{{ $c->id }}" @selected(old('clinic_id', $fee->clinic_id ?? session('active_clinic_id')) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $fee->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
            @foreach(\App\Models\Fee::CATEGORIES as $k=>$v)
                <option value="{{ $k }}" @selected(old('category', $fee->category ?? 'service')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Price (MMK)</label>
        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $fee->price ?? 0) }}">
    </div>
    <div class="col-md-4 d-flex align-items-center gap-4">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_foc" id="is_foc" value="1" @checked(old('is_foc', $fee->is_foc ?? false))>
            <label class="form-check-label" for="is_foc">FOC (price = 0)</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" @checked(old('is_active', $fee->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Fee</button>
    <a href="{{ route('fees.index') }}" class="btn btn-light">Cancel</a></div>
