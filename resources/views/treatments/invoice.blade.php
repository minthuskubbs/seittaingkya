<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice · Treatment #{{ $treatment->id }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>body{padding:24px;max-width:720px;margin:auto}@media print{.no-print{display:none}}</style>
</head>
<body onload="window.print && setTimeout(()=>{},300)">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h4 class="mb-0" style="color:#b30b0b">{{ $treatment->clinic->name ?? config('app.name') }}</h4>
            @if($treatment->clinic?->phone)<div class="text-muted small">Ph: {{ $treatment->clinic->phone }}</div>@endif
            @if($treatment->clinic?->address)<div class="text-muted small">{{ $treatment->clinic->address }}</div>@endif
        </div>
        <div class="text-end">
            <div class="h5 mb-0">INVOICE</div>
            <div class="small text-muted">TX-{{ $treatment->id }}</div>
            <div class="small">{{ $treatment->treatment_date?->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <strong>Patient:</strong> {{ $treatment->patient?->name ?? 'N/A' }}<br>
        <strong>Patient Code:</strong> {{ $treatment->patient?->patient_code ?? '—' }}<br>
        <strong>Doctor:</strong> {{ $treatment->doctor?->name ?? '—' }}
    </div>

    <table class="table">
        <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
        @foreach($treatment->treatmentTypes as $tt)
            <tr><td>{{ $tt->name }}@if($tt->pivot->qty > 1) ({{ $tt->pivot->qty }} × {{ money($tt->pivot->unit_price) }})@endif</td><td class="text-end">{{ money($tt->pivot->line_total) }}</td></tr>
        @endforeach
        @foreach($treatment->fees as $f)
            <tr><td>{{ $f->name }}@if($f->is_foc) (FOC)@endif</td><td class="text-end">{{ money($f->line_total) }}</td></tr>
        @endforeach
        @if($treatment->extraction_qty > 0)<tr><td>Tooth Extraction @if($treatment->extractionType) — {{ $treatment->extractionType->name }}@endif ({{ $treatment->extraction_qty }} × {{ money($treatment->extraction_price) }})</td><td class="text-end">{{ money($treatment->extractionTotal()) }}</td></tr>@endif
        @if($treatment->implant_qty > 0)<tr><td>Tooth Implant @if($treatment->implantType) — {{ $treatment->implantType->name }}@endif ({{ $treatment->implant_qty }} × {{ money($treatment->implant_price) }})</td><td class="text-end">{{ money($treatment->implantTotal()) }}</td></tr>@endif
        @if($treatment->surgery_charge > 0)<tr><td>Surgery</td><td class="text-end">{{ money($treatment->surgery_charge) }}</td></tr>@endif
        @if($treatment->denture_charge > 0)<tr><td>Denture@if($treatment->dentureType) — {{ $treatment->dentureType->name }}@endif</td><td class="text-end">{{ money($treatment->denture_charge) }}</td></tr>@endif
        @if($treatment->additional_charge > 0)<tr><td>Additional</td><td class="text-end">{{ money($treatment->additional_charge) }}</td></tr>@endif

        @foreach($treatment->sales as $sale)
            @foreach($sale->items as $it)
                <tr><td>{{ $it->name }} ({{ $it->quantity }} × {{ money($it->price) }}) <span class="text-muted">[Medicine]</span></td><td class="text-end">{{ money($it->line_total) }}</td></tr>
            @endforeach
        @endforeach
        </tbody>
        <tfoot>
            @if($treatment->discountAmount() > 0)
            <tr><td class="text-end">Subtotal</td><td class="text-end">{{ money($treatment->grossTotal()) }}</td></tr>
            <tr><td class="text-end">Discount @if($treatment->discount_type==='percent')({{ rtrim(rtrim(number_format($treatment->discount_value,2),'0'),'.') }}%)@endif</td><td class="text-end">- {{ money($treatment->discountAmount()) }}</td></tr>
            @endif
            <tr class="fw-bold"><td class="text-end">Total</td><td class="text-end">{{ money($treatment->invoiceTotal()) }}</td></tr>
            <tr><td class="text-end">Paid</td><td class="text-end">{{ money($treatment->paidAmount()) }}</td></tr>
            <tr class="fw-bold"><td class="text-end">Balance</td><td class="text-end">{{ money($treatment->balance()) }}</td></tr>
        </tfoot>
    </table>

    <div class="text-center text-muted small mt-4">Thank you for visiting {{ $treatment->clinic->name ?? config('app.name') }}.</div>
    <div class="text-center mt-3 no-print"><button class="btn" style="background:#b30b0b;color:#fff;border:0" onclick="window.print()">Print</button></div>
</body>
</html>
