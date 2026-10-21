<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #eee; }
        .sheet { max-width: 210mm; margin: 1rem auto; background: #fff; padding: 15mm; }
        @media print { body { background: #fff; } .sheet { margin: 0; padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
<div class="text-center no-print mt-3"><button class="btn btn-primary" onclick="window.print()">{{ __('Print') }}</button></div>
<div class="sheet">
    <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
        <div><h3 class="mb-0">{{ __(config('app.name')) }}</h3><div class="text-muted">{{ optional($invoice->branch)->name }} {{ optional($invoice->branch)->address }}</div><div class="text-muted">{{ optional($invoice->branch)->phone }}</div></div>
        <div class="text-end"><h4 class="mb-1">{{ __('Tax invoice') }}</h4><div class="fw-bold">{{ $invoice->invoice_number }}</div>
            <div class="small">{{ __('Date') }}: {{ $invoice->issue_date->format('Y-m-d') }}</div>
            <div class="small">{{ __('Due date') }}: {{ optional($invoice->due_date)->format('Y-m-d') ?: '-' }}</div></div>
    </div>
    <div class="mb-3"><div class="text-muted small">{{ __('Invoice to') }}</div>
        <div class="fw-bold">{{ $invoice->customer->name }}</div>
        <div>{{ $invoice->customer->company }}</div>
        <div>{{ $invoice->customer->phone }} {{ $invoice->customer->address }}</div></div>
    <table class="table table-bordered">
        <thead class="table-light"><tr><th style="width:50px">#</th><th>{{ __('Description') }}</th><th style="width:140px" class="text-end">{{ __('Amount') }}</th></tr></thead>
        <tbody>@foreach($invoice->items as $n => $it)<tr><td>{{ $n + 1 }}</td><td>{{ $it->description }}</td><td class="text-end">{{ $it->amount > 0 ? number_format($it->amount, 2) : '-' }}</td></tr>@endforeach</tbody>
        <tfoot>
            @php
                $itemsT = $invoice->itemsTotal();
                $shipT = $invoice->shippingTotal();
                $hasDiscount = $invoice->discount > 0;
                $hasTax = $invoice->tax_rate > 0 || $invoice->tax_amount > 0;
            @endphp
            @if($shipT > 0)
                @if($itemsT > 0)
                    <tr><td colspan="2" class="text-end">{{ __('Goods price') }}</td><td class="text-end">{{ number_format($itemsT, 2) }}</td></tr>
                @endif
                <tr class="fw-bold"><td colspan="2" class="text-end">{{ __('Shipping fee') }}</td><td class="text-end">{{ number_format($shipT, 2) }}</td></tr>
            @endif
            @if($hasDiscount || $hasTax)
                <tr><td colspan="2" class="text-end">{{ __('Subtotal') }}</td><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
            @endif
            @if($hasDiscount)
                <tr><td colspan="2" class="text-end">{{ __('Discount') }}</td><td class="text-end">- {{ number_format($invoice->discount, 2) }}</td></tr>
            @endif
            @if($hasTax)
                <tr><td colspan="2" class="text-end">{{ __('Tax') }} ({{ $invoice->tax_rate + 0 }}%)</td><td class="text-end">{{ number_format($invoice->tax_amount, 2) }}</td></tr>
            @endif
            <tr class="fw-bold "><td colspan="2" class="text-end">{{ __('Total due') }}</td><td class="text-end">{{ number_format($invoice->total, 2) }}</td></tr>
        </tfoot>
    </table>
    <div class="mt-3"><span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }} fs-6">{{ $invoice->statusLabel() }}</span></div>
    @if($invoice->notes)<p class="mt-3 small text-muted">{{ __('Notes') }}: {{ $invoice->notes }}</p>@endif
    <div class="text-center text-muted small mt-5 border-top pt-2">{{ __('Thank you for your business') }}</div>
</div>
</body>
</html>
