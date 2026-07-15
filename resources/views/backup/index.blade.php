@extends('layouts.app')
@section('title', 'Backup & Restore')
@section('content')
<div class="row g-3">
    <div class="col-lg-6"><div class="card"><div class="card-header"><i class="bi bi-download"></i> Create Backup</div><div class="card-body">
        <p class="text-muted small">Generates a full SQL dump of the database.</p>
        <form method="POST" action="{{ route('backup.create') }}"><button class="btn btn-brand"><i class="bi bi-hdd"></i> Backup Now</button></form>
    </div></div>
    <div class="card mt-3"><div class="card-header"><i class="bi bi-upload"></i> Restore</div><div class="card-body">
        <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" onsubmit="return confirm('Restoring will overwrite current data. Continue?')">
            @csrf
            <input type="file" name="sql_file" class="form-control mb-2" accept=".sql" required>
            <button class="btn btn-outline-danger"><i class="bi bi-arrow-counterclockwise"></i> Restore Database</button>
        </form>
    </div></div>
    </div>
    <div class="col-lg-6"><div class="card"><div class="card-header">Existing Backups</div>
        <div class="table-responsive"><table class="table mb-0 small">
            <thead><tr><th>File</th><th>Size</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($files as $f)
                <tr><td>{{ $f['name'] }}</td><td>{{ number_format($f['size']/1024,1) }} KB</td><td>{{ $f['date'] }}</td>
                    <td class="text-end"><a href="{{ route('backup.download',$f['name']) }}" class="btn btn-sm btn-light"><i class="bi bi-download"></i></a></td></tr>
            @empty<tr><td colspan="4" class="text-center text-muted py-3">No backups yet.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
