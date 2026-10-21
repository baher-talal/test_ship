@extends('layouts.app')
@section('title', $customer->name)
@section('heading', $customer->name)
@section('actions')
    <a href="{{ route('shipments.create', ['customer_id' => $customer->id]) }}" class="btn btn-success"><i class="bi bi-box-seam"></i> {{ __('New shipment') }}</a>
    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-outline-primary"><i class="bi bi-receipt"></i> {{ __('Issue an invoice') }}</a>
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-4 text-muted">{{ __('Company') }}</dt><dd class="col-8">{{ $customer->company ?: '-' }}</dd>
                <dt class="col-4 text-muted">{{ __('Phone') }}</dt><dd class="col-8">{{ $customer->phone }}</dd>
                <dt class="col-4 text-muted">{{ __('Mail') }}</dt><dd class="col-8">{{ $customer->email ?: '-' }}</dd>
                <dt class="col-4 text-muted">{{ __('City') }}</dt><dd class="col-8">{{ $customer->city ?: '-' }}</dd>
                <dt class="col-4 text-muted">{{ __('Address') }}</dt><dd class="col-8">{{ $customer->address ?: '-' }}</dd>
                <dt class="col-4 text-muted">{{ __('Notes') }}</dt><dd class="col-8">{{ $customer->notes ?: '-' }}</dd>
            </dl>
        </div></div>
        <div class="row g-2 text-center">
            <div class="col-4"><div class="card p-2"><div class="fs-4 fw-bold">{{ $totals['shipments'] }}</div><div class="small text-muted">{{ __('Shipments') }}</div></div></div>
            <div class="col-4"><div class="card p-2"><div class="fs-4 fw-bold text-success">{{ $totals['delivered'] }}</div><div class="small text-muted">{{ __('Delivered shipments') }}</div></div></div>
            <div class="col-4"><div class="card p-2"><div class="fs-5 fw-bold text-warning">{{ number_format($totals['unpaid'], 0) }}</div><div class="small text-muted">{{ __('Due') }}</div></div></div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header">{{ __('Customer shipments') }}</div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>{{ __('Tracking number') }}</th><th>{{ __('Receiver') }}</th><th>{{ __('To') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                @forelse($shipments as $s)
                    <tr>
                        <td><a href="{{ route('shipments.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->tracking_number }}</a></td>
                        <td>{{ $s->receiver_name }}</td><td>{{ $s->receiver_city }}</td>
                        <td>{{ number_format($s->shipping_cost, 2) }}</td>
                        <td>@include('partials.status-badge', ['shipment' => $s])</td>
                        <td class="small text-muted">{{ $s->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No shipments') }}</td></tr>
                @endforelse
                </tbody></table></div>
            <div class="card-footer bg-transparent">{{ $shipments->links() }}</div>
        </div>
    </div>
</div>
@endsection
