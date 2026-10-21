<div class="row g-3">
    @include('partials.input', ['name' => 'name', 'label' => __('Name'), 'required' => true, 'value' => $courier->name])
    @include('partials.input', ['name' => 'phone', 'label' => __('Phone'), 'required' => true, 'value' => $courier->phone])
    @include('partials.input', ['name' => 'national_id', 'label' => __('National ID'), 'value' => $courier->national_id])
    @include('partials.select', ['name' => 'vehicle_type', 'label' => __('Vehicle type'), 'required' => true, 'options' => \App\Models\Courier::VEHICLES, 'value' => $courier->vehicle_type])
    @include('partials.input', ['name' => 'vehicle_number', 'label' => __('Vehicle number'), 'value' => $courier->vehicle_number])
    @include('partials.select', ['name' => 'status', 'label' => __('Status'), 'required' => true, 'options' => \App\Models\Courier::STATUSES, 'value' => $courier->status])
    @if(auth()->user()->needsBranchChoice())
        @include('partials.select', ['name' => 'branch_id', 'label' => __('Branch'), 'options' => $branches->pluck('name', 'id')->all(), 'value' => $courier->branch_id, 'placeholder' => __('Select branch')])
    @endif
    @include('partials.select', ['name' => 'user_id', 'label' => __('Link to a login account (user with the courier role)'), 'options' => $users->pluck('name', 'id')->all(), 'value' => $courier->user_id, 'placeholder' => __('No account')])
    @include('partials.switch', ['name' => 'is_active', 'label' => __('Active courier'), 'value' => $courier->is_active])
</div>
