@extends('layouts.app')
@section('title', __('Couriers'))
@section('heading', __('Couriers'))
@section('actions')<a href="{{ route('couriers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New courier') }}</a>@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2">
        <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search by name / phone / vehicle number') }}"></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">{{ __('All statuses') }}</option>
            @foreach(\App\Models\Courier::STATUSES as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
        </select></div>
        <div class="col-auto"><button class="btn btn-primary">{{ __('Search') }}</button> <a href="{{ route('couriers.index') }}" class="btn btn-light">{{ __('Reset') }}</a></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Vehicle') }}</th><th>{{ __('Branch') }}</th><th>{{ __('Active shipments') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($couriers as $c)
            <tr>
                <td><a href="{{ route('couriers.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a>@unless($c->is_active) <span class="badge bg-secondary">{{ __('Suspended') }}</span>@endunless</td>
                <td>{{ $c->phone }}</td>
                <td>{{ __(\App\Models\Courier::VEHICLES[$c->vehicle_type] ?? $c->vehicle_type) }} {{ $c->vehicle_number }}</td>
                <td>{{ optional($c->branch)->name ?: '-' }}</td>
                <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ $c->active_shipments_count }}</span></td>
                <td><span class="badge bg-{{ ['available' => 'success', 'busy' => 'warning', 'off' => 'secondary'][$c->status] ?? 'secondary' }}">{{ $c->statusLabel() }}</span></td>
                <td class="text-nowrap">
                    <a href="{{ route('couriers.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @include('partials.delete-form', ['action' => route('couriers.destroy', $c)])
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">{{ __('No couriers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $couriers->links() }}</div></div>
@endsection
