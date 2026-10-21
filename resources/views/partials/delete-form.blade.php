<form method="post" action="{{ $action }}" data-confirm="{{ $confirm ?? __('Are you sure you want to delete?') }}" class="d-inline">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
</form>
