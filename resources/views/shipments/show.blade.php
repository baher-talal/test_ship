@extends('layouts.app')
@section('title', __('Shipment').' '.$shipment->tracking_number)
@section('heading', __('Shipment').' '.$shipment->tracking_number)

@section('actions')
    <a href="{{ route('shipments.label', $shipment) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-printer"></i> {{ __('Print waybill') }}</a>
    @if(auth()->user()->hasPermission('shipments.edit'))
        <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a>
    @endif
    @if(auth()->user()->hasPermission('shipments.delete') && !$shipment->invoice_id)
        <form method="post" action="{{ route('shipments.destroy', $shipment) }}" data-confirm="{{ __('The shipment will be deleted permanently. Continue?') }}">
            @csrf @method('DELETE') <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> {{ __('Delete') }}</button>
        </form>
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <div><div class="text-muted small">{{ __('Current status') }}</div><div class="fs-5">@include('partials.status-badge', ['shipment' => $shipment])</div></div>
                <div class="text-end"><div class="text-muted small">{{ __('Expected delivery') }}</div>
                    <strong class="{{ $shipment->isOverdue() ? 'text-danger' : '' }}">{{ optional($shipment->expected_delivery)->format('Y-m-d') ?: '-' }}</strong></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><div class="p-3 bg-light rounded h-100">
                    <div class="text-muted small mb-1"><i class="bi bi-person"></i> {{ __('Sender') }}</div>
                    <div class="fw-semibold">{{ optional($shipment->customer)->name }}</div>
                    <div class="small">{{ optional($shipment->customer)->phone }}</div>
                    <div class="small text-muted">{{ __('From') }}: {{ $shipment->origin_city }}</div>
                </div></div>
                <div class="col-md-6"><div class="p-3 bg-light rounded h-100">
                    <div class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> {{ __('Receiver') }}</div>
                    <div class="fw-semibold">{{ $shipment->receiver_name }}</div>
                    <div class="small"><a href="tel:{{ $shipment->receiver_phone }}">{{ $shipment->receiver_phone }}</a></div>
                    <div class="small text-muted">{{ $shipment->receiver_city }} - {{ $shipment->receiver_address }}</div>
                </div></div>
            </div>
            <hr>
            <div class="row g-3 small">
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Service') }}</div><strong>{{ $shipment->serviceLabel() }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Weight / Pieces') }}</div><strong>{{ $shipment->weight }} {{ __('kg') }} / {{ $shipment->pieces }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Shipping fee') }}</div><strong>{{ number_format($shipment->shipping_cost, 2) }}</strong> <span class="text-muted">({{ $shipment->payerLabel() }})</span></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Goods price') }}</div><strong>{{ number_format($shipment->goods_value, 2) }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Total') }}</div><strong>{{ number_format($shipment->total_amount, 2) }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('To collect from the receiver') }}</div><strong class="{{ $shipment->cod_amount > 0 ? 'text-success' : '' }}">{{ number_format($shipment->cod_amount, 2) }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Collection') }}</div><strong>{{ $shipment->paymentLabel() }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Reference number') }}</div><strong>{{ $shipment->reference_number ?: '-' }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Pickup from sender') }}</div><strong>{{ __(\App\Models\Shipment::PICKUP_TYPES[$shipment->pickup_type] ?? '-') }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Content type') }}</div><strong>{{ $shipment->packageLabel() }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Dimensions (cm)') }}</div><strong>{{ $shipment->length ? $shipment->length.' × '.$shipment->width.' × '.$shipment->height : '-' }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Receiver additional phone') }}</div><strong>{{ $shipment->receiver_phone2 ?: '-' }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('District / Landmark') }}</div><strong>{{ $shipment->receiver_district ?: '-' }} / {{ $shipment->receiver_landmark ?: '-' }}</strong></div>
                <div class="col-12">
                    @if($shipment->is_fragile)<span class="badge bg-danger-subtle text-danger border"><i class="bi bi-exclamation-triangle"></i> {{ __('Fragile') }}</span>@endif
                    @if($shipment->allow_open)<span class="badge bg-info-subtle text-info-emphasis border">{{ __('May be opened before receiving') }}</span>@endif
                </div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Branch') }}</div><strong>{{ optional($shipment->branch)->name ?: '-' }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Courier') }}</div><strong>{{ optional($shipment->courier)->name ?: __('Unassigned') }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Created by') }}</div><strong>{{ optional($shipment->creator)->name ?: '-' }}</strong></div>
                <div class="col-6 col-md-3"><div class="text-muted">{{ __('Invoice') }}</div>
                    <strong>@if($shipment->invoice)<a href="{{ route('invoices.show', $shipment->invoice) }}">{{ $shipment->invoice->invoice_number }}</a>@else - @endif</strong></div>
                @if($shipment->description)<div class="col-12"><div class="text-muted">{{ __('Contents') }}</div>{{ $shipment->description }}</div>@endif
                @if($shipment->notes)<div class="col-12"><div class="text-muted">{{ __('Notes') }}</div>{{ $shipment->notes }}</div>@endif
            </div>
        </div></div>

        <div class="card"><div class="card-header"><i class="bi bi-clock-history"></i> {{ __('Shipment tracking history') }}</div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($shipment->histories as $i => $h)
                        <div class="timeline-item {{ $i === 0 ? 'first' : '' }}">
                            <div class="d-flex justify-content-between flex-wrap">
                                <span class="fw-semibold"><span class="badge bg-{{ $h->statusColor() }} {{ in_array($h->statusColor(), ['warning','info']) ? 'text-dark' : '' }}">{{ $h->statusLabel() }}</span></span>
                                <span class="small text-muted">{{ $h->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            @if($h->note)<div class="small mt-1">{{ __($h->note) }}</div>@endif
                            <div class="small text-muted">
                                @if($h->location)<i class="bi bi-geo-alt"></i> {{ $h->location }} @endif
                                @if($h->user)· {{ __('by') }} {{ $h->user->name }}@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">{{ __('Update status') }}</div>
            <div class="card-body">
                <form method="post" action="{{ route('shipments.status', $shipment) }}" class="vstack gap-2">
                    @csrf
                    <select name="status" class="form-select" required>
                        @foreach($allowedStatuses as $k => $m)
                            <option value="{{ $k }}" {{ $shipment->status === $k ? 'selected' : '' }}>{{ __($m['label']) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="location" class="form-control" placeholder="{{ __('Current location (optional)') }}">
                    <input type="text" name="note" class="form-control" placeholder="{{ __('Note (optional)') }}">
                    <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> {{ __('Update') }}</button>
                </form>
            </div>
        </div>

        @if(auth()->user()->hasPermission('shipments.assign'))
        <div class="card"><div class="card-header">{{ __('Assign to a courier') }}</div>
            <div class="card-body">
                <form method="post" action="{{ route('shipments.assign', $shipment) }}" class="vstack gap-2">
                    @csrf
                    <select name="courier_id" class="form-select" required>
                        <option value="">{{ __('Select courier') }}</option>
                        @foreach($couriers as $c)
                            <option value="{{ $c->id }}" {{ $shipment->courier_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->statusLabel() }})</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-primary"><i class="bi bi-person-check"></i> {{ __('Assign') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
