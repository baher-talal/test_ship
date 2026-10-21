@php $col = $col ?? 12; @endphp
<div class="col-md-{{ $col }}">
    <label class="form-label" for="f_{{ $name }}">{{ $label }}</label>
    <textarea id="f_{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}" class="form-control @error($name) is-invalid @enderror">{{ old($name, $value ?? null) }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
