@extends('layouts.app')
@section('title', __('Dashboard'))
@section('heading', __('Dashboard'))

@section('actions')
    @if(auth()->user()->hasPermission('shipments.create'))
        <a href="{{ route('shipments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New shipment') }}</a>
    @endif
@endsection

@section('content')
@php
    $cards = [
        [__('Total shipments'), $stats['total'], 'bi-boxes', 'primary'],
        [__('Today shipments'), $stats['today'], 'bi-calendar-day', 'info'],
        [__('In progress'), $stats['active'], 'bi-truck', 'warning'],
        [__('Delivered this month'), $stats['delivered_month'], 'bi-check2-circle', 'success'],
        [__('Overdue shipments'), $stats['overdue'], 'bi-exclamation-triangle', 'danger'],
        [__('Collect').' '.'COD ('.__('Delivered (total)').')', number_format($stats['cod_pending'], 2), 'bi-cash-coin', 'secondary'],
    ];
    if (isset($stats['revenue_month'])) {
        $cards[] = [__('Revenue this month'), number_format($stats['revenue_month'], 2), 'bi-graph-up-arrow', 'success'];
        $cards[] = [__('Unpaid invoices'), number_format($stats['unpaid_invoices'], 2), 'bi-receipt', 'warning'];
        $cards[] = [__('Available couriers'), $stats['couriers_available'], 'bi-bicycle', 'info'];
    }
@endphp

<div class="row g-3 mb-4">
    @foreach($cards as $c)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-{{ $c[3] }}-subtle text-{{ $c[3] }}"><i class="bi {{ $c[2] }}"></i></div>
                <div><div class="text-muted small">{{ $c[0] }}</div><div class="fs-4 fw-bold">{{ $c[1] }}</div></div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100"><div class="card-header">{{ __('Shipments in the last 7 days') }}</div>
            <div class="card-body"><canvas id="dayChart" height="110"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100"><div class="card-header">{{ __('Status distribution') }}</div>
            <div class="card-body"><canvas id="statusChart"></canvas></div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-header d-flex justify-content-between">{{ __('Latest shipments') }}
            <a href="{{ route('shipments.index') }}" class="small">{{ __('View all') }}</a></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>{{ __('Tracking number') }}</th><th>{{ __('Customer') }}</th><th>{{ __('To') }}</th><th>{{ __('Courier') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @forelse($latest as $s)
                        <tr>
                            <td><a href="{{ route('shipments.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->tracking_number }}</a></td>
                            <td>{{ optional($s->customer)->name }}</td>
                            <td>{{ $s->receiver_city }}</td>
                            <td>{{ optional($s->courier)->name ?: '-' }}</td>
                            <td>@include('partials.status-badge', ['shipment' => $s])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No shipments yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header text-danger"><i class="bi bi-alarm"></i> {{ __('Delay alerts') }}</div>
            <div class="list-group list-group-flush">
                @forelse($overdueList as $s)
                    <a href="{{ route('shipments.show', $s) }}" class="list-group-item list-group-item-action">
                        <div class="fw-semibold">{{ $s->tracking_number }}</div>
                        <div class="small text-muted">{{ optional($s->customer)->name }} - {{ __('was expected on') }} {{ $s->expected_delivery->format('Y-m-d') }}</div>
                    </a>
                @empty
                    <div class="list-group-item text-center text-muted py-4"><i class="bi bi-emoji-smile"></i> {{ __('No overdue shipments') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = 'Cairo';
    new Chart(document.getElementById('dayChart'), {
        type: 'bar',
        data: { labels: @json($dayChart['labels']), datasets: [{ label: @json(__('Number of shipments')), data: @json($dayChart['data']), backgroundColor: '#4da3ff', borderRadius: 6 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: @json($statusChart['labels']), datasets: [{ data: @json($statusChart['data']),
            backgroundColor: ['#adb5bd', '#0dcaf0', '#0d6efd', '#ffc107', '#198754', '#212529', '#dc3545'] }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endpush
