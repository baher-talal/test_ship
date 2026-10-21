@extends('layouts.app')
@section('title', __('Branches'))
@section('heading', __('Branches'))
@section('actions')<a href="{{ route('branches.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New branch') }}</a>@endsection
@section('content')
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Branch') }}</th><th>{{ __('Code') }}</th><th>{{ __('City') }}</th><th>{{ __('Users') }}</th><th>{{ __('Couriers') }}</th><th>{{ __('Customers') }}</th><th>{{ __('Shipments') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($branches as $b)
            <tr>
                <td class="fw-semibold">{{ $b->name }}<div class="small text-muted">{{ $b->phone }}</div></td>
                <td><code>{{ $b->code }}</code></td><td>{{ $b->city }}</td>
                <td>{{ $b->users_count }}</td><td>{{ $b->couriers_count }}</td><td>{{ $b->customers_count }}</td><td>{{ $b->shipments_count }}</td>
                <td>{!! $b->is_active ? '<span class="badge bg-success">'.__('Active').'</span>' : '<span class="badge bg-secondary">'.__('Suspended').'</span>' !!}</td>
                <td class="text-nowrap">
                    <a href="{{ route('branches.edit', $b) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @include('partials.delete-form', ['action' => route('branches.destroy', $b)])
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-5">{{ __('No branches') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $branches->links() }}</div></div>
@endsection
