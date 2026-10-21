@extends('layouts.app')
@section('title', __('Edit role'))
@section('heading', __('Edit role').': '.$role->name)
@section('actions')<a href="{{ route('roles.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('roles.update', $role) }}">@csrf @method('PUT')
    <div class="card"><div class="card-body">@include('roles._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save changes') }}</button></div></div>
</form>
@endsection
