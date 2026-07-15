@csrf
@php $currentRole = old('role', isset($user) ? $user->getRoleNames()->first() : 'clinic_admin'); @endphp
<div class="row g-3" x-data="{ role: '{{ $currentRole }}' }">
    <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name',$user->name??'') }}" required></div>
    <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" value="{{ old('email',$user->email??'') }}" required></div>
    <div class="col-md-6"><label class="form-label">Role <span class="text-danger">*</span></label>
        <select name="role" class="form-select" x-model="role" required>
            @foreach($roles as $r)<option value="{{ $r }}" @selected($currentRole===$r)>{{ ucwords(str_replace('_',' ',$r)) }}</option>@endforeach
        </select></div>
    <div class="col-md-6" x-show="role !== 'super_admin'">
        <label class="form-label">Clinic <span class="text-danger">*</span></label>
        <select name="clinic_id" class="form-select">
            <option value="">— Select clinic —</option>
            @foreach($clinics as $c)<option value="{{ $c->id }}" @selected(old('clinic_id',$user->clinic_id??'')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <div class="form-text">Clinic admins only see data for the selected clinic.</div>
    </div>
    <div class="col-md-6"><label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="{{ old('phone',$user->phone??'') }}"></div>
    <div class="col-md-6"><label class="form-label">Password {{ isset($user) ? '(leave blank to keep)' : '' }}</label>
        <input type="password" name="password" class="form-control" {{ isset($user)?'':'required' }}></div>

    <div class="col-12"><div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_active" id="ua" value="1" @checked(old('is_active',$user->is_active??true))>
        <label class="form-check-label" for="ua">Active</label></div></div>
</div>
<div class="mt-4"><button class="btn btn-brand">Save User</button>
    <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a></div>
