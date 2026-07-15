<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><title>Receipt {{ $sale->sale_no }}</title>
<style>
body{font-family:monospace;max-width:320px;margin:auto;padding:12px;font-size:13px}
h4{text-align:center;margin:.2rem 0;color:#b30b0b}
table{width:100%;border-collapse:collapse}td{padding:2px 0}
.r{text-align:right}.c{text-align:center}
hr{border:none;border-top:1px dashed #999}
@media print{.no-print{display:none}}
</style></head>
<body onload="window.print()">
<h4>{{ $sale->clinic->name ?? config('app.name') }}</h4>
@if($sale->clinic?->phone)<div class="c">Ph: {{ $sale->clinic->phone }}</div>@endif
@if($sale->clinic?->address)<div class="c">{{ $sale->clinic->address }}</div>@endif
<div class="c">Receipt: {{ $sale->sale_no }}</div>
<div class="c">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
<hr>
<table>
@foreach($sale->items as $it)
    <tr><td>{{ $it->name }}</td></tr>
    <tr><td>{{ $it->quantity }} x {{ number_format($it->price) }}</td><td class="r">{{ number_format($it->line_total) }}</td></tr>
@endforeach
</table>
<hr>
<table>
    <tr><td>Subtotal</td><td class="r">{{ number_format($sale->subtotal) }}</td></tr>
    <tr><td>Discount</td><td class="r">{{ number_format($sale->discount) }}</td></tr>
    <tr><td><strong>TOTAL</strong></td><td class="r"><strong>{{ number_format($sale->total) }} MMK</strong></td></tr>
</table>
<hr>
<div class="c">Customer: {{ $sale->patient->name ?? $sale->customer_name ?? 'Walk-in' }}</div>
@if($sale->patient)<div class="c">Patient Code: {{ $sale->patient->patient_code }}</div>@endif
<div class="c">Thank you!</div>
<div class="c no-print" style="margin-top:10px"><button onclick="window.print()">Print</button></div>
</body></html>
