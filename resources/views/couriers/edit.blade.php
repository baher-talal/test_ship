@extends('layouts.app')
@section('title', __('Edit courier'))
@section('heading', __('Edit courier').': '.$courier->name)
@section('actions')<a href="{{ route('couriers.show', $courier) }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('couriers.update', $courier) }}">@csrf @method('PUT')
    <div class="card"><div class="card-body">@include('couriers._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save changes') }}</button></div></div>
</form>
@endsection
