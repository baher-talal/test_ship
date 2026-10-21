@extends('layouts.app')
@section('title', __('Edit user'))
@section('heading', __('Edit user').': '.$user->name)
@section('actions')<a href="{{ route('users.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('users.update', $user) }}">@csrf @method('PUT')
    <div class="card"><div class="card-body">@include('users._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save changes') }}</button></div></div>
</form>
@endsection
