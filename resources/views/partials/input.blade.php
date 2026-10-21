@php
    $type = $type ?? 'text';
    $col = $col ?? 6;
    $val = old($name, $value ?? null);
@endphp
<div class="col-md-{{ $col }}">
    <label class="form-label" for="f_{{ $name }}">{{ $label }} @if(!empty($required))<span class="text-danger">*</span>@endif</label>
    <input type="{{ $type }}" id="f_{{ $name }}" name="{{ $name }}"
           value="{{ $type === 'password' ? '' : $val }}"
           class="form-control @error($name) is-invalid @enderror"
           @if(!empty($required)) required @endif
           @if(isset($step)) step="{{ $step }}" @endif
           @if(isset($min)) min="{{ $min }}" @endif
           @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
