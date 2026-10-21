{{-- Variables: $permissionGroups, $selected (array of keys), $grantable (optional: the keys that may be granted) --}}
@php $grantable = $grantable ?? null; @endphp
<div class="row g-3">
    @foreach($permissionGroups as $gKey => $group)
        <div class="col-md-6 col-xl-4">
            <div class="border rounded h-100">
                <div class="bg-light px-3 py-2 fw-semibold d-flex justify-content-between align-items-center">
                    {{ __($group['label']) }}
                    <button type="button" class="btn btn-link btn-sm p-0 perm-toggle-group" data-group="{{ $gKey }}">{{ __('Select all') }}</button>
                </div>
                <div class="p-3 vstack gap-2">
                    @foreach($group['items'] as $key => $label)
                        @php $locked = $grantable !== null && ! in_array($key, $grantable, true); @endphp
                        <div class="form-check">
                            <input class="form-check-input perm-check" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}"
                                   data-key="{{ $key }}" data-group="{{ $gKey }}" data-own="{{ in_array($key, $selected, true) ? 1 : 0 }}" data-locked="{{ $locked ? 1 : 0 }}"
                                   {{ in_array($key, old('permissions', $selected), true) ? 'checked' : '' }} {{ $locked ? 'disabled' : '' }}>
                            <label class="form-check-label small" for="perm_{{ $key }}">{{ __($label) }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
<script>
    document.querySelectorAll('.perm-toggle-group').forEach(function (b) {
        b.addEventListener('click', function () {
            var boxes = document.querySelectorAll('.perm-check[data-group="' + b.dataset.group + '"]:not(:disabled)');
            var all = Array.prototype.every.call(boxes, function (c) { return c.checked; });
            boxes.forEach(function (c) { c.checked = !all; c.dispatchEvent(new Event('change')); });
        });
    });
</script>
