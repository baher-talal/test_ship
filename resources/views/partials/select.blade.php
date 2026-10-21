@php
    $col = $col ?? 6;
    $val = old($name, $value ?? null);
@endphp
<div class="col-md-{{ $col }}">
    <label class="form-label" for="f_{{ $name }}">{{ $label }} @if(!empty($required))<span class="text-danger">*</span>@endif</label>
    <select id="f_{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" @if(!empty($required)) required @endif>
        @if(isset($placeholder))<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $k => $text)
            <option value="{{ $k }}" {{ (string) $val === (string) $k ? 'selected' : '' }}>{{ __($text) }}</option>
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
