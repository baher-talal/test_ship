<div class="row g-3">
    @include('partials.input', ['name' => 'name', 'label' => __('Name'), 'required' => true, 'value' => $customer->name])
    @include('partials.input', ['name' => 'company', 'label' => __('Company'), 'value' => $customer->company])
    @include('partials.input', ['name' => 'phone', 'label' => __('Phone'), 'required' => true, 'value' => $customer->phone])
    @include('partials.input', ['name' => 'email', 'label' => __('Email'), 'type' => 'email', 'value' => $customer->email])
    @include('partials.input', ['name' => 'city', 'label' => __('City'), 'value' => $customer->city])
    @include('partials.input', ['name' => 'address', 'label' => __('Address'), 'value' => $customer->address])
    @if(auth()->user()->needsBranchChoice())
        @include('partials.select', ['name' => 'branch_id', 'label' => __('Branch'), 'options' => $branches->pluck('name', 'id')->all(), 'value' => $customer->branch_id, 'placeholder' => __('No branch')])
    @endif
    @include('partials.textarea', ['name' => 'notes', 'label' => __('Notes'), 'value' => $customer->notes])
    @include('partials.switch', ['name' => 'is_active', 'label' => __('Active customer'), 'value' => $customer->is_active])
</div>
