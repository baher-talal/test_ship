@extends('layouts.app')
@section('title', __('Notifications'))
@section('heading', __('Notifications'))
@section('actions')
    <form method="post" action="{{ route('notifications.readAll') }}">@csrf <button class="btn btn-outline-primary">{{ __('Mark all as read') }}</button></form>
@endsection
@section('content')
<div class="card"><div class="list-group list-group-flush">
    @forelse($notifications as $n)
        <a href="{{ route('notifications.read', $n->id) }}" class="list-group-item list-group-item-action d-flex gap-3 {{ $n->read_at ? '' : 'bg-light' }}">
            <i class="bi bi-circle-fill text-{{ $n->type }} mt-2" style="font-size:.6rem"></i>
            <div class="flex-grow-1">
                <div class="{{ $n->read_at ? '' : 'fw-semibold' }}">{{ $n->title }}</div>
                <div class="small text-muted">{{ $n->message }}</div>
            </div>
            <div class="small text-muted text-nowrap">{{ $n->created_at->diffForHumans() }}</div>
        </a>
    @empty
        <div class="list-group-item text-center text-muted py-5">{{ __('No notifications') }}</div>
    @endforelse
</div><div class="card-footer bg-transparent">{{ $notifications->links() }}</div></div>
@endsection
