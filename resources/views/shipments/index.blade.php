@extends('layouts.app')
@section('title', __('Shipments'))
@section('heading', auth()->user()->isCourier() ? __('My shipments') : __('Shipments'))

@section('actions')
    @if(auth()->user()->hasPermission('shipments.create'))
        <a href="{{ route('shipments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New shipment') }}</a>
    @endif
@endsection

@section('content')
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">{{ __('Search') }}</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Tracking no. / receiver / phone / customer') }}"></div>
        <div class="col-md-2"><label class="form-label small">{{ __('Status') }}</label>
            <select name="status" class="form-select"><option value="">{{ __('All') }}</option>
                @foreach(\App\Models\Shipment::STATUSES as $k => $m)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ __($m['label']) }}</option>@endforeach
            </select></div>
        @if($couriers->count())
        <div class="col-md-2"><label class="form-label small">{{ __('Courier') }}</label>
            <select name="courier_id" class="form-select"><option value="">{{ __('All') }}</option>
                @foreach($couriers as $c)<option value="{{ $c->id }}" {{ (string) request('courier_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select></div>
        @endif
        @if($branches->count())
        <div class="col-md-2"><label class="form-label small">{{ __('Branch') }}</label>
            <select name="branch_id" class="form-select"><option value="">{{ __('All') }}</option>
                @foreach($branches as $b)<option value="{{ $b->id }}" {{ (string) request('branch_id') === (string) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach
            </select></div>
        @endif
        <div class="col-md-2"><label class="form-label small">{{ __('From date') }}</label><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label small">{{ __('To date') }}</label><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
        <div class="col-md-auto d-flex gap-2 align-items-center">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="overdue" value="1" id="ov" {{ request('overdue') ? 'checked' : '' }}><label class="form-check-label" for="ov">{{ __('Overdue only') }}</label></div>
            <button class="btn btn-primary"><i class="bi bi-funnel"></i> {{ __('Filter') }}</button>
            <a href="{{ route('shipments.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
        </div>
    </form>
</div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>{{ __('Tracking number') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Receiver') }}</th><th>{{ __('To') }}</th><th>{{ __('Service') }}</th><th>{{ __('Cost') }}</th><th>COD</th><th>{{ __('Courier') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($shipments as $s)
                <tr>
                    <td><a href="{{ route('shipments.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->tracking_number }}</a></td>
                    <td>{{ optional($s->customer)->name }}</td>
                    <td>{{ $s->receiver_name }}<div class="small text-muted">{{ $s->receiver_phone }}</div></td>
                    <td>{{ $s->receiver_city }}</td>
                    <td>{{ $s->serviceLabel() }}</td>
                    <td>{{ number_format($s->shipping_cost, 2) }}</td>
                    <td>{{ $s->cod_amount > 0 ? number_format($s->cod_amount, 2) : '-' }}</td>
                    <td>{{ optional($s->courier)->name ?: '-' }}</td>
                    <td>@include('partials.status-badge', ['shipment' => $s])</td>
                    <td class="small text-muted">{{ $s->created_at->format('Y-m-d') }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('shipments.show', $s) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                        @if(auth()->user()->hasPermission('shipments.edit'))
                            <a href="{{ route('shipments.edit', $s) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-5">{{ __('No matching shipments') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $shipments->links() }}</div>
</div>
@endsection
