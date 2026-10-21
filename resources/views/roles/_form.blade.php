<div class="row g-3">
    @include('partials.input', ['name' => 'name', 'label' => __('Role name'), 'required' => true, 'value' => $role->name])
    @if($role->exists)
        <div class="col-md-6"><label class="form-label">{{ __('Identifier') }}</label><input class="form-control" value="{{ $role->slug }}" disabled></div>
    @else
        @include('partials.input', ['name' => 'slug', 'label' => __('Identifier (English, no spaces)'), 'required' => true, 'value' => null, 'placeholder' => __('Example').': '.'operations_lead'])
    @endif
    @include('partials.input', ['name' => 'level', 'label' => __('Hierarchy level (1 is the highest - a bigger number means a lower rank)'), 'type' => 'number', 'min' => 2, 'required' => true, 'value' => $role->level])
    @include('partials.select', ['name' => 'parent_id', 'label' => __('Reports to role (above it in the hierarchy)'), 'options' => $parents->pluck('name', 'id')->all(), 'value' => $role->parent_id, 'placeholder' => __('None')])
    @include('partials.input', ['name' => 'description', 'label' => __('Short description'), 'col' => 12, 'value' => $role->description])

    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2">{{ __('Role permissions') }}</h6></div>
    <div class="col-12">@include('partials.permission-picker', ['permissionGroups' => $permissionGroups, 'selected' => $selected])</div>
</div>
