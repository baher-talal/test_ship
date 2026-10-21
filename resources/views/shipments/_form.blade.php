@php $authUser = auth()->user(); @endphp
<div class="row g-3">
    <div class="col-12"><h6 class="text-primary border-bottom pb-2">{{ __('Sender and pickup details') }}</h6></div>
    <div class="col-md-6">
        <label class="form-label" for="f_sender_name">{{ __('Sender name') }} <span class="text-danger">*</span></label>
        <input type="text" id="f_sender_name" name="sender_name" list="customersList" autocomplete="off" required
               value="{{ old('sender_name', optional($sender)->name) }}"
               class="form-control @error('sender_name') is-invalid @enderror" placeholder="{{ __('Type the sender name (or pick an existing customer)') }}">
        @error('sender_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <datalist id="customersList">
            @foreach($customers as $c)<option value="{{ $c->name }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}"></option>@endforeach
        </datalist>
        <div class="form-text">{{ __('A new sender is added automatically to the customers page after saving') }}.</div>
    </div>
    @include('partials.input', ['name' => 'sender_phone', 'label' => __('Sender phone'), 'required' => true, 'value' => optional($sender)->phone])
    @include('partials.input', ['name' => 'sender_address', 'label' => __('Sender address'), 'value' => optional($sender)->address])
    @include('partials.input', ['name' => 'origin_city', 'label' => __('Origin city'), 'required' => true, 'value' => $shipment->origin_city ?: optional($authUser->branch)->city])
    @include('partials.select', ['name' => 'pickup_type', 'label' => __('Pickup method'), 'required' => true, 'options' => \App\Models\Shipment::PICKUP_TYPES, 'value' => $shipment->pickup_type ?: 'pickup'])
    @include('partials.input', ['name' => 'reference_number', 'label' => __('Reference number / sender order number'), 'value' => $shipment->reference_number, 'placeholder' => __('Optional - order number in the customer store')])
    @if($authUser->needsBranchChoice() && !$shipment->exists)
        @include('partials.select', ['name' => 'branch_id', 'label' => __('Branch'), 'required' => true, 'options' => $branches->pluck('name', 'id')->all(), 'value' => null, 'placeholder' => __('Select branch')])
    @endif

    <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">{{ __('Receiver details') }}</h6></div>
    @include('partials.input', ['name' => 'receiver_name', 'label' => __('Receiver name'), 'required' => true, 'value' => $shipment->receiver_name])
    @include('partials.input', ['name' => 'receiver_phone', 'label' => __('Receiver phone'), 'required' => true, 'value' => $shipment->receiver_phone, 'col' => 3])
    @include('partials.input', ['name' => 'receiver_phone2', 'label' => __('Additional phone'), 'value' => $shipment->receiver_phone2, 'col' => 3])
    @include('partials.input', ['name' => 'receiver_city', 'label' => __('City / Governorate'), 'required' => true, 'value' => $shipment->receiver_city, 'col' => 4])
    @include('partials.input', ['name' => 'receiver_district', 'label' => __('District / Area'), 'value' => $shipment->receiver_district, 'col' => 4])
    @include('partials.input', ['name' => 'receiver_landmark', 'label' => __('Nearest landmark'), 'value' => $shipment->receiver_landmark, 'col' => 4])
    @include('partials.input', ['name' => 'receiver_address', 'label' => __('Receiver address in detail'), 'required' => true, 'col' => 12, 'value' => $shipment->receiver_address])

    <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">{{ __('Shipment details') }}</h6></div>
    @include('partials.select', ['name' => 'service_type', 'label' => __('Service type'), 'required' => true, 'options' => \App\Models\Shipment::SERVICES, 'value' => $shipment->service_type, 'col' => 3])
    @include('partials.select', ['name' => 'package_type', 'label' => __('Content type'), 'required' => true, 'options' => \App\Models\Shipment::PACKAGE_TYPES, 'value' => $shipment->package_type ?: 'parcel', 'col' => 3])
    @include('partials.input', ['name' => 'weight', 'label' => __('Weight (kg)'), 'type' => 'number', 'step' => '0.01', 'min' => '0.01', 'required' => true, 'value' => $shipment->weight, 'col' => 3])
    @include('partials.input', ['name' => 'pieces', 'label' => __('Number of pieces'), 'type' => 'number', 'min' => '1', 'required' => true, 'value' => $shipment->pieces, 'col' => 3])
    @include('partials.input', ['name' => 'length', 'label' => __('Length (cm)'), 'type' => 'number', 'step' => '0.1', 'min' => '0', 'value' => $shipment->length, 'col' => 3])
    @include('partials.input', ['name' => 'width', 'label' => __('Width (cm)'), 'type' => 'number', 'step' => '0.1', 'min' => '0', 'value' => $shipment->width, 'col' => 3])
    @include('partials.input', ['name' => 'height', 'label' => __('Height (cm)'), 'type' => 'number', 'step' => '0.1', 'min' => '0', 'value' => $shipment->height, 'col' => 3])
    @include('partials.input', ['name' => 'expected_delivery', 'label' => __('Expected delivery'), 'type' => 'date', 'value' => optional($shipment->expected_delivery)->format('Y-m-d'), 'col' => 3])
    @include('partials.input', ['name' => 'description', 'label' => __('Contents description'), 'value' => $shipment->description])
    @include('partials.select', ['name' => 'courier_id', 'label' => __('Courier (optional)'), 'options' => $couriers->pluck('name', 'id')->all(), 'value' => $shipment->courier_id, 'placeholder' => __('Do not assign now')])
    @include('partials.switch', ['name' => 'is_fragile', 'label' => __('Fragile shipment (handle with care)'), 'value' => (bool) $shipment->is_fragile])
    @include('partials.switch', ['name' => 'allow_open', 'label' => __('Allow the receiver to open the shipment before receiving'), 'value' => (bool) $shipment->allow_open])

    <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">{{ __('Cost and payment') }}</h6></div>
    @include('partials.input', ['name' => 'shipping_cost', 'label' => __('Shipping fee'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'required' => true, 'value' => $shipment->shipping_cost, 'col' => 4])
    @include('partials.input', ['name' => 'goods_value', 'label' => __('Goods price'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $shipment->goods_value ?: 0, 'col' => 4])
    <div class="col-md-4">
        <label class="form-label" for="f_total_amount">{{ __('Total (shipping + goods price)') }}</label>
        <input type="text" id="f_total_amount" class="form-control bg-light fw-bold" readonly value="{{ number_format($shipment->total_amount, 2, '.', '') }}">
    </div>
    @include('partials.select', ['name' => 'payment_method', 'label' => __('Collect goods price'), 'required' => true, 'options' => \App\Models\Shipment::PAYMENTS, 'value' => $shipment->payment_method ?: 'prepaid', 'col' => 4])
    @include('partials.select', ['name' => 'shipping_payer', 'label' => __('Who pays the shipping fee?'), 'required' => true, 'options' => \App\Models\Shipment::PAYERS, 'value' => $shipment->shipping_payer ?: 'sender', 'col' => 4])
    <div class="col-md-4">
        <label class="form-label" for="f_collect_amount">{{ __('To collect from the receiver') }}</label>
        <input type="text" id="f_collect_amount" class="form-control bg-light fw-bold text-success" readonly value="{{ number_format($shipment->cod_amount, 2, '.', '') }}">
    </div>
    <div class="col-12"><div class="form-text mt-0"><i class="bi bi-info-circle"></i> {{ __('Amount to collect') }} = ({{ __('goods price if collected from the receiver) + (shipping fee if paid by the receiver). Calculated automatically and saved with the shipment') }}.</div></div>
    <div class="col-12"><button type="button" class="btn btn-sm btn-outline-secondary" id="estimateBtn"><i class="bi bi-calculator"></i> {{ __('Estimate the shipping fee automatically from weight and service type') }}</button></div>
    @include('partials.textarea', ['name' => 'notes', 'label' => __('Notes and delivery instructions'), 'value' => $shipment->notes])
</div>

@push('scripts')
<script>
    (function () {
        var $ = function (id) { return document.getElementById(id); };
        // {{ __('Auto-fill sender phone and address when an existing customer is chosen') }}
        $('f_sender_name').addEventListener('change', function () {
            var opts = document.querySelectorAll('#customersList option');
            for (var i = 0; i < opts.length; i++) {
                if (opts[i].value === $('f_sender_name').value) {
                    $('f_sender_phone').value = opts[i].dataset.phone || '';
                    $('f_sender_address').value = opts[i].dataset.address || '';
                    break;
                }
            }
        });
        var base = @json(config('shipping.base_price'));
        var perKg = @json(config('shipping.price_per_kg'));
        function calc() {
            var ship = parseFloat($('f_shipping_cost').value) || 0;
            var goods = parseFloat($('f_goods_value').value) || 0;
            var collect = ($('f_payment_method').value === 'cod' ? goods : 0) + ($('f_shipping_payer').value === 'receiver' ? ship : 0);
            $('f_total_amount').value = (ship + goods).toFixed(2);
            $('f_collect_amount').value = collect.toFixed(2);
        }
        $('estimateBtn').addEventListener('click', function () {
            var svc = $('f_service_type').value;
            var w = parseFloat($('f_weight').value) || 1;
            $('f_shipping_cost').value = ((base[svc] || 0) + Math.max(0, w - 1) * (perKg[svc] || 0)).toFixed(2);
            calc();
        });
        ['f_shipping_cost', 'f_goods_value', 'f_payment_method', 'f_shipping_payer'].forEach(function (id) {
            $(id).addEventListener('input', calc); $(id).addEventListener('change', calc);
        });
        calc();
    })();
</script>
@endpush
