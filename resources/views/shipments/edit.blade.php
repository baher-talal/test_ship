@extends('layouts.app')
@section('title', __('Edit shipment'))
@section('heading', __('Edit shipment').' '.$shipment->tracking_number)
@section('actions')<a href="{{ route('shipments.show', $shipment) }}" class="btn btn-light">{{ __('Back') }}</a>@endsection

@section('content')
<form method="post" action="{{ route('shipments.update', $shipment) }}">
    @csrf @method('PUT')
    <div class="card"><div class="card-body">
        @include('shipments._form')
    </div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4"><i class="bi bi-check-lg"></i> {{ __('Save changes') }}</button></div></div>
</form>
@endsection
