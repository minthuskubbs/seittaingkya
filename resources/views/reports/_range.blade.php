<form class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-brand"><i class="bi bi-funnel"></i> Filter</button></div>
</div></form>
