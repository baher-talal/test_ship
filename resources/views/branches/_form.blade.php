<div class="row g-3">
    @include('partials.input', ['name' => 'name', 'label' => __('Branch name'), 'required' => true, 'value' => $branch->name])
    @include('partials.input', ['name' => 'code', 'label' => __('Branch code'), 'required' => true, 'value' => $branch->code])
    @include('partials.input', ['name' => 'city', 'label' => __('City'), 'required' => true, 'value' => $branch->city])
    @include('partials.input', ['name' => 'phone', 'label' => __('Phone'), 'value' => $branch->phone])
    @include('partials.input', ['name' => 'address', 'label' => __('Address'), 'col' => 12, 'value' => $branch->address])
    @include('partials.switch', ['name' => 'is_active', 'label' => __('Active branch'), 'value' => $branch->is_active])
</div>
