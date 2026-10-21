@extends('layouts.app')
@section('title', __('New customer'))
@section('heading', __('Add customer'))
@section('actions')<a href="{{ route('customers.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('customers.store') }}">@csrf
    <div class="card"><div class="card-body">@include('customers._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save') }}</button></div></div>
</form>
@endsection
