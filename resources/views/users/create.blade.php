@extends('layouts.app')
@section('title', __('New user'))
@section('heading', __('Add user'))
@section('actions')<a href="{{ route('users.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('users.store') }}">@csrf
    <div class="card"><div class="card-body">@include('users._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save') }}</button></div></div>
</form>
@endsection
