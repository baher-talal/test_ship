@extends('layouts.app')
@section('title', __('Roles and permissions'))
@section('heading', __('Roles and permissions (job hierarchy)'))
@section('actions')<a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New role') }}</a>@endsection
@section('content')
<div class="alert alert-info small"><i class="bi bi-info-circle"></i> {{ __('Roles are sorted by job hierarchy (highest first). A user can hold several roles and their permissions are combined. A user cannot grant a role at or above their own level') }}.</div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Role') }}</th><th>{{ __('Level') }}</th><th>{{ __('Users') }}</th><th>{{ __('Permissions') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($rows as $role)
            <tr>
                <td style="padding-inline-start: {{ 0.85 + $role->depth * 1.6 }}rem">
                    @if($role->depth)<span class="text-muted">└</span> @endif
                    <span class="fw-semibold">{{ __($role->name) }}</span> <code class="small">{{ $role->slug }}</code>
                    @if($role->is_system)<span class="badge bg-secondary-subtle text-secondary">{{ __('Primary') }}</span>@endif
                    @if($role->description)<div class="small text-muted">{{ __($role->description) }}</div>@endif
                </td>
                <td><span class="badge bg-primary-subtle text-primary">{{ $role->level }}</span></td>
                <td>{{ $role->users_count }}</td>
                <td class="small">{{ $role->isAdmin() ? __('All permissions') : count($role->permissionKeys()).' '.__('permission') }}</td>
                <td class="text-nowrap">
                    @unless($role->isAdmin())
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @endunless
                    @unless($role->is_system)@include('partials.delete-form', ['action' => route('roles.destroy', $role)])@endunless
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
