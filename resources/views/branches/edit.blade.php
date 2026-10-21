@extends('layouts.app')
@section('title', __('Edit branch'))
@section('heading', __('Edit branch').': '.$branch->name)
@section('actions')<a href="{{ route('branches.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('branches.update', $branch) }}">@csrf @method('PUT')
    <div class="card"><div class="card-body">@include('branches._form')</div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4">{{ __('Save changes') }}</button></div></div>
</form>
@endsection
