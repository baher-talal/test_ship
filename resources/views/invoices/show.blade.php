@extends('layouts.app')
@section('title', __('Invoice').' '.$invoice->invoice_number)
@section('heading', __('Invoice').' '.$invoice->invoice_number)
@section('actions')
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-printer"></i> {{ __('Print') }}</a>
    @if($invoice->status === 'unpaid' && auth()->user()->hasPermission('invoices.pay'))
        <form method="post" action="{{ route('invoices.pay', $invoice) }}" data-confirm="{{ __('Confirm recording the invoice payment?') }}">@csrf
            <button class="btn btn-success"><i class="bi bi-cash-stack"></i> {{ __('Record payment') }}</button></form>
        <form method="post" action="{{ route('invoices.cancel', $invoice) }}" data-confirm="{{ __('The invoice will be cancelled and its shipments released. Continue?') }}">@csrf
            <button class="btn btn-outline-danger">{{ __('Cancel invoice') }}</button></form>
    @endif
    @if(auth()->user()->hasPermission('invoices.edit'))
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a>
    @endif
    @if(auth()->user()->hasPermission('invoices.delete'))
        <form method="post" action="{{ route('invoices.destroy', $invoice) }}" data-confirm="{{ __('The invoice will be deleted permanently and its shipments released. Continue?') }}">@csrf @method('DELETE')
            <button class="btn btn-danger"><i class="bi bi-trash"></i> {{ __('Delete') }}</button></form>
    @endif
@endsection

@section('content')
<div class="card"><div class="card-body">
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="text-muted small">{{ __('Customer') }}</div><a href="{{ route('customers.show', $invoice->customer) }}" class="fw-semibold">{{ $invoice->customer->name }}</a></div>
        <div class="col-md-2"><div class="text-muted small">{{ __('Issue date') }}</div>{{ $invoice->issue_date->format('Y-m-d') }}</div>
        <div class="col-md-2"><div class="text-muted small">{{ __('Due date') }}</div>{{ optional($invoice->due_date)->format('Y-m-d') ?: '-' }}</div>
        <div class="col-md-2"><div class="text-muted small">{{ __('Branch') }}</div>{{ optional($invoice->branch)->name ?: '-' }}</div>
        <div class="col-md-2"><div class="text-muted small">{{ __('Status') }}</div><span class="badge bg-{{ $invoice->statusColor() }} {{ $invoice->statusColor() === 'warning' ? 'text-dark' : '' }}">{{ $invoice->statusLabel() }}</span>
            @if($invoice->paid_at)<div class="small text-muted">{{ $invoice->paid_at->format('Y-m-d') }}</div>@endif</div>
    </div>
    <table class="table">
        <thead><tr><th>#</th><th>{{ __('Description') }}</th><th class="text-end">{{ __('Amount') }}</th></tr></thead>
        <tbody>
        @foreach($invoice->items as $n => $it)
            <tr><td>{{ $n + 1 }}</td>
                <td>{{ $it->description }}
                @if($it->shipment)<a href="{{ route('shipments.show', $it->shipment) }}" class="small">({{ __('View') }})</a>@endif</td>
                <td class="text-end">{{ $it->amount > 0 ? number_format($it->amount, 2) : '-' }}</td></tr>
        @endforeach
        </tbody>
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
            <tr class="fw-bold fs-5"><td colspan="2" class="text-end">{{ __('Total') }}</td><td class="text-end">{{ number_format($invoice->total, 2) }}</td></tr>
        </tfoot>
    </table>
    @if($invoice->notes)<div class="text-muted small">{{ __('Notes') }}: {{ $invoice->notes }}</div>@endif
</div></div>
@endsection
