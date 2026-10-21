@extends('layouts.app')
@section('title', __('New shipment'))
@section('heading', __('Create a new shipment'))
@section('actions')<a href="{{ route('shipments.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection

@section('content')
<form method="post" action="{{ route('shipments.store') }}">
    @csrf
    <div class="card"><div class="card-body">
        @include('shipments._form')
    </div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4"><i class="bi bi-check-lg"></i> {{ __('Save shipment') }}</button></div></div>
</form>
@endsection
