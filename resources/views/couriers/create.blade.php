@extends('layouts.app')
@section('title', __('New courier'))
@section('heading', __('Add courier'))
@section('actions')<a href="{{ route('couriers.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('couriers.store') }}">@csrf
    <div class="card"><div class="card-body">@include('couriers._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save') }}</button></div></div>
</form>
@endsection
