@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $expenseType->name ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $expenseType->sort_order ?? 0) }}"></div>
    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="eta" value="1" @checked(old('is_active', $expenseType->is_active ?? true))>
        <label class="form-check-label" for="eta">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save Expense Type</button>
    <a href="{{ route('expense-types.index') }}" class="btn btn-light">Cancel</a></div>
