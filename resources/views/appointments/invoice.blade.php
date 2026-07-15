<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $appointment->appointment_no }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>body{padding:24px;max-width:720px;margin:auto}@media print{.no-print{display:none}}</style>
</head>
<body onload="window.print && setTimeout(()=>{},300)">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h4 class="mb-0" style="color:#b30b0b">{{ $appointment->clinic->name ?? config('app.name') }}</h4>
            @if($appointment->clinic?->phone)<div class="text-muted small">Ph: {{ $appointment->clinic->phone }}</div>@endif
            @if($appointment->clinic?->address)<div class="text-muted small">{{ $appointment->clinic->address }}</div>@endif
        </div>
        <div class="text-end">
            <div class="h5 mb-0">INVOICE</div>
            <div class="small text-muted">{{ $appointment->appointment_no }}</div>
            <div class="small">{{ $appointment->scheduled_at->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <strong>Patient:</strong> {{ $appointment->patient?->name ?? 'N/A' }} ({{ $appointment->patient?->patient_code }})<br>
        <strong>Doctor:</strong> {{ $appointment->doctor->name ?? '—' }}
    </div>

    <table class="table">
        <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
        @foreach($appointment->fees as $f)
            <tr><td>{{ $f->name }} @if($f->is_foc)(FOC)@endif</td><td class="text-end">{{ money($f->line_total) }}</td></tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold"><td class="text-end">Total</td><td class="text-end">{{ money($appointment->total_amount) }}</td></tr>
            <tr><td class="text-end">Paid</td><td class="text-end">{{ money($appointment->paidAmount()) }}</td></tr>
            <tr class="fw-bold"><td class="text-end">Balance</td><td class="text-end">{{ money($appointment->balance()) }}</td></tr>
        </tfoot>
    </table>

    <div class="text-center text-muted small mt-4">Thank you for visiting {{ $appointment->clinic->name ?? config('app.name') }}.</div>
    <div class="text-center mt-3 no-print"><button class="btn btn-brand" style="background:#b30b0b;border:0" onclick="window.print()">Print</button></div>
</body>
</html>
