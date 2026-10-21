<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Waybill') }} {{ $shipment->tracking_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #eee; }
        .label-box { width: 148mm; margin: 1rem auto; background: #fff; border: 2px solid #000; padding: 6mm; }
        .barcode { font-family: 'Libre Barcode 39', monospace; font-size: 3rem; letter-spacing: 3px; }
        @media print { body { background: #fff; } .label-box { margin: 0; } .no-print { display: none; } }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>
<div class="text-center no-print mt-3"><button class="btn btn-primary" onclick="window.print()">{{ __('Print') }}</button></div>
<div class="label-box">
    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
        <div><strong class="fs-5">{{ __(config('app.name')) }}</strong><div class="small">{{ optional($shipment->branch)->name }}</div></div>
        <div class="text-end"><div class="small">{{ $shipment->created_at->format('Y-m-d') }}</div><div class="fw-bold">{{ $shipment->serviceLabel() }}</div></div>
    </div>
    <div class="text-center my-2">
        <div class="barcode">*{{ $shipment->tracking_number }}*</div>
        <div class="fw-bold fs-5">{{ $shipment->tracking_number }}</div>
    </div>
    <div class="row g-0 border-top border-bottom py-2 my-2">
        <div class="col-6 pe-2 border-start-0">
            <div class="small text-muted">{{ __('From (sender)') }}</div>
            <div class="fw-bold">{{ optional($shipment->customer)->name }}</div>
            <div>{{ optional($shipment->customer)->phone }}</div>
            <div class="small">{{ $shipment->origin_city }}</div>
        </div>
        <div class="col-6 ps-2 border-end">
            <div class="small text-muted">{{ __('To (receiver)') }}</div>
            <div class="fw-bold fs-5">{{ $shipment->receiver_name }}</div>
            <div>{{ $shipment->receiver_phone }}</div>
            <div>{{ $shipment->receiver_city }}@if($shipment->receiver_district) - {{ $shipment->receiver_district }}@endif - {{ $shipment->receiver_address }}</div>
            @if($shipment->receiver_landmark)<div class="small">{{ __('Landmark') }}: {{ $shipment->receiver_landmark }}</div>@endif
            @if($shipment->receiver_phone2)<div class="small">{{ __('Phone 2') }}: {{ $shipment->receiver_phone2 }}</div>@endif
        </div>
    </div>
    <div class="row text-center">
        <div class="col-3"><div class="small text-muted">{{ __('Weight') }}</div><strong>{{ $shipment->weight }} {{ __('kg') }}</strong></div>
        <div class="col-3"><div class="small text-muted">{{ __('Pieces') }}</div><strong>{{ $shipment->pieces }}</strong></div>
        <div class="col-3"><div class="small text-muted">{{ __('Payment') }}</div><strong>{{ $shipment->payment_method === 'cod' ? 'COD' : __('Prepaid') }}</strong></div>
        <div class="col-3"><div class="small text-muted">{{ __('To collect') }}</div><strong class="fs-5">{{ $shipment->cod_amount > 0 ? number_format($shipment->cod_amount, 2) : '0' }}</strong></div>
    </div>
    <div class="row text-center mt-2 border-top pt-2 small">
        <div class="col-4">{{ __('Shipping') }}: <strong>{{ number_format($shipment->shipping_cost, 2) }}</strong> ({{ $shipment->payerLabel() }})</div>
        <div class="col-4">{{ __('Goods price') }}: <strong>{{ number_format($shipment->goods_value, 2) }}</strong></div>
        <div class="col-4">{{ __('Total') }}: <strong>{{ number_format($shipment->total_amount, 2) }}</strong></div>
    </div>
    @if($shipment->reference_number)<div class="small mt-1">{{ __('Ref') }}: {{ $shipment->reference_number }}</div>@endif
    @if($shipment->is_fragile)<div class="fw-bold text-danger mt-1">⚠ {{ __('Fragile - handle with care') }}</div>@endif
    @if($shipment->allow_open)<div class="small">{{ __('The receiver may open the shipment before receiving') }}</div>@endif
    @if($shipment->description)<div class="small mt-2">{{ __('Contents') }}: {{ $shipment->description }}</div>@endif
</div>
</body>
</html>
