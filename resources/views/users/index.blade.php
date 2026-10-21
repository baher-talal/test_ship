@extends('layouts.app')
@section('title', __('Users'))
@section('heading', __('Users'))
@section('actions')
    @if(auth()->user()->hasPermission('roles.manage'))<a href="{{ route('roles.index') }}" class="btn btn-outline-secondary"><i class="bi bi-shield-lock"></i> {{ __('Roles and permissions') }}</a>@endif
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New user') }}</a>
@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2">
        <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search by name / email / phone') }}"></div>
        <div class="col-md-3"><select name="role" class="form-select"><option value="">{{ __('All roles') }}</option>
            @foreach($roles as $r)<option value="{{ $r->id }}" {{ (string) request('role') === (string) $r->id ? 'selected' : '' }}>{{ __($r->name) }}</option>@endforeach
        </select></div>
        <div class="col-auto"><button class="btn btn-primary">{{ __('Search') }}</button> <a href="{{ route('users.index') }}" class="btn btn-light">{{ __('Reset') }}</a></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Mail') }}</th><th>{{ __('Roles') }}</th><th>{{ __('Workplace') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td class="fw-semibold">{{ $u->name }}<div class="small text-muted">{{ $u->phone }}</div></td>
                <td>{{ $u->email }}</td>
                <td>@foreach($u->roles as $r)<span class="badge bg-primary-subtle text-primary">{{ __($r->name) }}</span> @endforeach</td>
                <td class="small">{{ $u->workplaceLabel() }}<div class="text-muted">{{ __(\App\Models\User::WORK_TYPES[$u->work_type] ?? '') }}</div></td>
                <td>{!! $u->is_active ? '<span class="badge bg-success">'.__('Active').'</span>' : '<span class="badge bg-secondary">'.__('Suspended').'</span>' !!}</td>
                <td class="text-nowrap">
                    @if($u->id == auth()->id() || auth()->user()->canManageUser($u))
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    @endif
                    @if($u->id != auth()->id() && auth()->user()->canManageUser($u))@include('partials.delete-form', ['action' => route('users.destroy', $u)])@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $users->links() }}</div></div>
@endsection
