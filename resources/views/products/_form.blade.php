@csrf
<div class="row g-3">
    @include('partials.clinic_select', ['selectedClinicId' => $product->clinic_id ?? null])
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name',$product->name??'') }}" required></div>
    <div class="col-md-3"><label class="form-label">SKU</label>
        <input name="sku" class="form-control" value="{{ old('sku',$product->sku??'') }}"></div>
    <div class="col-md-3"><label class="form-label">Type</label>
        <select name="type" class="form-select">
            <option value="medicine" @selected(old('type',$product->type??'medicine')==='medicine')>Medicine</option>
            <option value="dental_supply" @selected(old('type',$product->type??'')==='dental_supply')>Dental Supply</option>
        </select></div>
    <div class="col-md-3"><label class="form-label">Unit</label>
        <input name="unit" class="form-control" value="{{ old('unit',$product->unit??'pcs') }}"></div>
    <div class="col-md-3"><label class="form-label">Cost Price</label>
        <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price',$product->cost_price??0) }}"></div>
    <div class="col-md-3"><label class="form-label">Sale Price</label>
        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price',$product->sale_price??0) }}"></div>
    <div class="col-md-3"><label class="form-label">Low Stock Alert &le;</label>
        <input type="number" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold',$product->low_stock_threshold??2) }}"></div>
    <div class="col-md-3"><label class="form-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', isset($product) && $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}"></div>
    @if(!isset($product))
    <div class="col-md-3"><label class="form-label">Opening Stock</label>
        <input type="number" name="stock_qty" class="form-control" value="{{ old('stock_qty',0) }}"></div>
    @endif
    <div class="col-md-6"><label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select"><option value="">— None —</option>
            @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('supplier_id',$product->supplier_id??'')==$s->id)>{{ $s->name }}</option>@endforeach
        </select></div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="pa" value="1" @checked(old('is_active',$product->is_active??true))>
        <label class="form-check-label" for="pa">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Product</button>
    <a href="{{ route('products.index') }}" class="btn btn-light">Cancel</a></div>
