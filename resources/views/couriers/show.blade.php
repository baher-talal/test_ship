@extends('layouts.app')
@section('title', $courier->name)
@section('heading', __('Courier').': '.$courier->name)
@section('actions')<a href="{{ route('couriers.edit', $courier) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a>@endsection
@section('content')
<div class="row g-3 mb-3">
    @foreach([[__('Total shipments'), $stats['total'], 'primary'], [__('Active shipments'), $stats['active'], 'warning'], [__('Delivered shipments'), $stats['delivered'], 'success'], [__('Returned'), $stats['returned'], 'dark']] as $s)
        <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="fs-3 fw-bold text-{{ $s[2] }}">{{ $s[1] }}</div><div class="small text-muted">{{ $s[0] }}</div></div></div>
    @endforeach
</div>
<div class="row g-3">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <dl class="row mb-0 small">
            <dt class="col-5 text-muted">{{ __('Phone') }}</dt><dd class="col-7">{{ $courier->phone }}</dd>
            <dt class="col-5 text-muted">{{ __('National ID') }}</dt><dd class="col-7">{{ $courier->national_id ?: '-' }}</dd>
            <dt class="col-5 text-muted">{{ __('Vehicle') }}</dt><dd class="col-7">{{ __(\App\Models\Courier::VEHICLES[$courier->vehicle_type] ?? '') }} {{ $courier->vehicle_number }}</dd>
            <dt class="col-5 text-muted">{{ __('Branch') }}</dt><dd class="col-7">{{ optional($courier->branch)->name ?: '-' }}</dd>
            <dt class="col-5 text-muted">{{ __('Status') }}</dt><dd class="col-7">{{ $courier->statusLabel() }}</dd>
            <dt class="col-5 text-muted">{{ __('Login account') }}</dt><dd class="col-7">{{ optional($courier->user)->email ?: __('Not linked') }}</dd>
        </dl>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="card-header">{{ __('Courier shipments') }}</div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>{{ __('Tracking number') }}</th><th>{{ __('Customer') }}</th><th>{{ __('To') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
            @forelse($shipments as $s)
                <tr>
                    <td><a href="{{ route('shipments.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->tracking_number }}</a></td>
                    <td>{{ optional($s->customer)->name }}</td><td>{{ $s->receiver_city }}</td>
                    <td>@include('partials.status-badge', ['shipment' => $s])</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No shipments') }}</td></tr>
            @endforelse
            </tbody></table></div>
        <div class="card-footer bg-transparent">{{ $shipments->links() }}</div>
    </div></div>
</div>
@endsection
