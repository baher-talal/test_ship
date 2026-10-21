@extends('layouts.app')
@section('title', __('Edit customer'))
@section('heading', __('Edit customer').': '.$customer->name)
@section('actions')<a href="{{ route('customers.show', $customer) }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('customers.update', $customer) }}">@csrf @method('PUT')
    <div class="card"><div class="card-body">@include('customers._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save changes') }}</button></div></div>
</form>
@endsection
