@extends('layouts.app')
@section('title', __('My account'))
@section('heading', __('My account'))
@section('content')
<form method="post" action="{{ route('profile.update') }}" style="max-width:720px">
    @csrf @method('PUT')
    <div class="card"><div class="card-body">
        <div class="row g-3">
            @include('partials.input', ['name' => 'name', 'label' => __('Name'), 'required' => true, 'value' => $user->name])
            @include('partials.input', ['name' => 'phone', 'label' => __('Phone'), 'value' => $user->phone])
            <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input class="form-control" value="{{ $user->email }}" disabled></div>
            <div class="col-md-6"><label class="form-label">{{ __('Role / Branch') }}</label><input class="form-control" value="{{ $user->roleLabel() }} - {{ $user->workplaceLabel() }}" disabled></div>
            <div class="col-12"><hr><h6>{{ __('Change password (optional)') }}</h6></div>
            @include('partials.input', ['name' => 'current_password', 'label' => __('Current password'), 'type' => 'password', 'col' => 4])
            @include('partials.input', ['name' => 'password', 'label' => __('New password'), 'type' => 'password', 'col' => 4])
            @include('partials.input', ['name' => 'password_confirmation', 'label' => __('Confirm password'), 'type' => 'password', 'col' => 4])
        </div>
    </div><div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save') }}</button></div></div>
</form>
@endsection
