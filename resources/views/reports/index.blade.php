@extends('layouts.app')
@section('title', __('Reports'))
@section('heading', __('Reports'))
@section('actions')
    <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-success"><i class="bi bi-file-earmark-spreadsheet"></i> {{ __('Export') }} CSV</a>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i> {{ __('Print') }}</button>
@endsection
@section('content')
<div class="card mb-3 no-print"><div class="card-body">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label small">{{ __('From') }}</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label small">{{ __('To') }}</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control"></div>
        @if($branches->count())
        <div class="col-md-2"><label class="form-label small">{{ __('Branch') }}</label><select name="branch_id" class="form-select"><option value="">{{ __('All') }}</option>
            @foreach($branches as $b)<option value="{{ $b->id }}" {{ (string) request('branch_id') === (string) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach</select></div>
        @endif
        <div class="col-md-2"><label class="form-label small">{{ __('Courier') }}</label><select name="courier_id" class="form-select"><option value="">{{ __('All') }}</option>
            @foreach($couriers as $c)<option value="{{ $c->id }}" {{ (string) request('courier_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-primary">{{ __('Show report') }}</button></div>
    </form>
</div></div>

<p class="text-muted">{{ __('Period') }}: {{ $from->format('Y-m-d') }} {{ __('To') }} {{ $to->format('Y-m-d') }}</p>

<div class="row g-3 mb-3">
    @foreach([
        [__('Total shipments'), $summary['total'], 'primary'], [__('Delivered'), $summary['delivered'], 'success'],
        [__('Success rate %'), $summary['delivery_rate'], 'info'], [__('Returns'), $summary['returned'], 'dark'],
        [__('Cancelled'), $summary['cancelled'], 'danger'], [__('Shipping revenue'), number_format($summary['revenue'], 2), 'success'],
        ['COD '.__('collected'), number_format($summary['cod_collected'], 2), 'warning'], [__('Total weight (kg)'), number_format($summary['weight'], 1), 'secondary'],
        [__('Paid invoices'), number_format($invoices['paid'], 2), 'success'], [__('Unpaid invoices'), number_format($invoices['unpaid'], 2), 'warning'],
    ] as $c)
        <div class="col-6 col-md-3"><div class="card p-3"><div class="small text-muted">{{ $c[0] }}</div><div class="fs-4 fw-bold text-{{ $c[2] }}">{{ $c[1] }}</div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6"><div class="card"><div class="card-header">{{ __('By status') }}</div>
        <table class="table mb-0"><thead><tr><th>{{ __('Status') }}</th><th>{{ __('Count') }}</th><th>{{ __('Revenue') }}</th></tr></thead><tbody>
        @foreach(\App\Models\Shipment::STATUSES as $k => $m)
            <tr><td><span class="badge bg-{{ $m['color'] }} {{ in_array($m['color'], ['warning','info']) ? 'text-dark' : '' }}">{{ __($m['label']) }}</span></td>
                <td>{{ optional($byStatus->get($k))->c ?: 0 }}</td><td>{{ number_format(optional($byStatus->get($k))->revenue ?: 0, 2) }}</td></tr>
        @endforeach</tbody></table></div></div>

    <div class="col-lg-6"><div class="card"><div class="card-header">{{ __('Top destinations') }}</div>
        <table class="table mb-0"><thead><tr><th>{{ __('City') }}</th><th>{{ __('Number of shipments') }}</th></tr></thead><tbody>
        @forelse($byCity as $r)<tr><td>{{ $r->city }}</td><td>{{ $r->c }}</td></tr>@empty<tr><td colspan="2" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>@endforelse
        </tbody></table></div></div>

    <div class="col-lg-6"><div class="card"><div class="card-header">{{ __('Courier performance') }}</div>
        <table class="table mb-0"><thead><tr><th>{{ __('Courier') }}</th><th>{{ __('Total') }}</th><th>{{ __('Delivered (total)') }}</th><th>{{ __('Returned (total)') }}</th><th>{{ __('Success rate') }}</th></tr></thead><tbody>
        @forelse($byCourier as $r)
            <tr><td>{{ $courierNames[$r->courier_id] ?? '-' }}</td><td>{{ $r->total }}</td><td>{{ $r->delivered }}</td><td>{{ $r->returned }}</td>
                <td>{{ $r->total ? round($r->delivered / $r->total * 100) : 0 }}%</td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>@endforelse
        </tbody></table></div></div>

    @if($byBranch->count())
    <div class="col-lg-6"><div class="card"><div class="card-header">{{ __('Branch performance') }}</div>
        <table class="table mb-0"><thead><tr><th>{{ __('Branch') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Revenue') }}</th></tr></thead><tbody>
        @foreach($byBranch as $r)<tr><td>{{ $r->name }}</td><td>{{ $r->total }}</td><td>{{ number_format($r->revenue, 2) }}</td></tr>@endforeach
        </tbody></table></div></div>
    @endif
</div>
@endsection
