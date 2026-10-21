@extends('layouts.app')
@section('title', __('Invoices'))
@section('heading', __('Invoices'))
@section('actions')<a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New invoice') }}</a>@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">{{ __('Total unpaid') }}</div><div class="fs-4 fw-bold text-warning">{{ number_format($summary['unpaid'], 2) }}</div></div></div>
    <div class="col-md-6"><div class="card p-3"><div class="text-muted small">{{ __('Total paid') }}</div><div class="fs-4 fw-bold text-success">{{ number_format($summary['paid'], 2) }}</div></div></div>
</div>
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2">
        <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Invoice number or customer name') }}"></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">{{ __('All statuses') }}</option>
            @foreach(\App\Models\Invoice::STATUSES as $k => $m)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ __($m['label']) }}</option>@endforeach
        </select></div>
        <div class="col-auto"><button class="btn btn-primary">{{ __('Search') }}</button> <a href="{{ route('invoices.index') }}" class="btn btn-light">{{ __('Reset') }}</a></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Invoice number') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th><th>{{ __('Due date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($invoices as $i)
            <tr>
                <td><a href="{{ route('invoices.show', $i) }}" class="fw-semibold text-decoration-none">{{ $i->invoice_number }}</a></td>
                <td>{{ optional($i->customer)->name }}</td>
                <td>{{ $i->issue_date->format('Y-m-d') }}</td>
                <td>{{ optional($i->due_date)->format('Y-m-d') ?: '-' }}</td>
                <td class="fw-semibold">{{ number_format($i->total, 2) }}</td>
                <td><span class="badge bg-{{ $i->statusColor() }} {{ $i->statusColor() === 'warning' ? 'text-dark' : '' }}">{{ $i->statusLabel() }}</span></td>
                <td class="text-nowrap">
                    <a href="{{ route('invoices.print', $i) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="{{ __('Print') }}"><i class="bi bi-printer"></i></a>
                    @if(auth()->user()->hasPermission('invoices.edit'))
                        <a href="{{ route('invoices.edit', $i) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                    @endif
                    @if(auth()->user()->hasPermission('invoices.delete'))
                        @include('partials.delete-form', ['action' => route('invoices.destroy', $i), 'confirm' => __('The invoice will be deleted and its shipments released. Continue?')])
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">{{ __('No invoices') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $invoices->links() }}</div></div>
@endsection
