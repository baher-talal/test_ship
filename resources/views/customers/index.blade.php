@extends('layouts.app')
@section('title', __('Customers'))
@section('heading', __('Customers'))
@section('actions')<a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New customer') }}</a>@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2">
        <div class="col-md-5"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search by name / phone / company / email') }}"></div>
        <div class="col-auto"><button class="btn btn-primary"><i class="bi bi-search"></i> {{ __('Search') }}</button> <a href="{{ route('customers.index') }}" class="btn btn-light">{{ __('Reset') }}</a></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Company') }}</th><th>{{ __('Phone') }}</th><th>{{ __('City') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($customers as $c)
            <tr>
                <td><a href="{{ route('customers.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->name }}</a></td>
                <td>{{ $c->company ?: '-' }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->city ?: '-' }}</td>
                <td><span class="badge bg-primary-subtle text-primary">{{ $c->shipments_count }}</span></td>
                <td>{!! $c->is_active ? '<span class="badge bg-success">'.__('Active').'</span>' : '<span class="badge bg-secondary">'.__('Suspended').'</span>' !!}</td>
                <td class="text-nowrap">
                    <a href="{{ route('shipments.create', ['customer_id' => $c->id]) }}" class="btn btn-sm btn-outline-success" title="{{ __('New shipment') }}"><i class="bi bi-box-seam"></i></a>
                    <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @if(auth()->user()->hasPermission('customers.delete'))@include('partials.delete-form', ['action' => route('customers.destroy', $c)])@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">{{ __('No customers') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $customers->links() }}</div></div>
@endsection
