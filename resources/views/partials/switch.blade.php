<div class="col-12">
    <div class="form-check form-switch">
        <input type="hidden" name="{{ $name }}" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="f_{{ $name }}" name="{{ $name }}" value="1" {{ old($name, $value ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="f_{{ $name }}">{{ $label }}</label>
    </div>
</div>
