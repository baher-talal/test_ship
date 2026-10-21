@extends('layouts.app')
@section('title', __('New invoice'))
@section('heading', __('Issue an invoice'))
@section('actions')<a href="{{ route('invoices.index') }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-6"><label class="form-label">{{ __('Select customer (sender)') }}</label>
            <select name="customer_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- {{ __('Select') }} --</option>
                @foreach($customers as $c)<option value="{{ $c->id }}" {{ $customer && $customer->id == $c->id ? 'selected' : '' }}>{{ $c->name }} - {{ $c->phone }}</option>@endforeach
            </select></div>
        <div class="col-auto"><button class="btn btn-outline-primary">{{ __('Show customer shipments') }}</button></div>
    </form>
</div></div>

@if($customer)
<form method="post" action="{{ route('invoices.store') }}">
    @csrf
    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

    <div class="card mb-3"><div class="card-body">
        <label class="form-label fw-semibold">{{ __('Invoice basis (can be issued for any shipment: prepaid or') }} COD)</label>
        <div class="row g-2">
            @php $basisOld = old('basis', 'shipping'); @endphp
            <div class="col-lg-4"><label class="border rounded p-2 d-flex gap-2 h-100" style="cursor:pointer">
                <input class="form-check-input mt-1" type="radio" name="basis" value="shipping" {{ $basisOld === 'shipping' ? 'checked' : '' }}>
                <span><strong>{{ __('Shipping fee only') }}</strong><span class="d-block small text-muted">{{ __('Shipping service invoice for the sender') }}</span></span></label></div>
            <div class="col-lg-4"><label class="border rounded p-2 d-flex gap-2 h-100" style="cursor:pointer">
                <input class="form-check-input mt-1" type="radio" name="basis" value="total" {{ $basisOld === 'total' ? 'checked' : '' }}>
                <span><strong>{{ __('Total (shipping + goods price)') }}</strong><span class="d-block small text-muted">{{ __('When the sender asks for an invoice with the full shipment value') }}</span></span></label></div>
            <div class="col-lg-4"><label class="border rounded p-2 d-flex gap-2 h-100" style="cursor:pointer">
                <input class="form-check-input mt-1" type="radio" name="basis" value="collect" {{ $basisOld === 'collect' ? 'checked' : '' }}>
                <span><strong>{{ __('To collect from the receiver') }}</strong><span class="d-block small text-muted">{{ __('Goods price + shipping according to each shipment setting') }}</span></span></label></div>
        </div>
    </div></div>

    <div class="card mb-3"><div class="card-header">{{ __('Shipments') }} {{ $customer->name }} {{ __('not invoiced') }}</div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th style="width:40px"><input type="checkbox" id="checkAll" class="form-check-input" checked></th>
                <th>{{ __('Tracking number') }}</th><th>{{ __('Receiver') }}</th><th>{{ __('Status') }}</th><th>{{ __('Collection') }}</th><th>{{ __('Shipping') }}</th><th>{{ __('Goods price') }}</th><th>{{ __('Total') }}</th><th>{{ __('To collect') }}</th></tr></thead>
            <tbody>
            @forelse($shipments as $s)
                <tr>
                    <td><input type="checkbox" name="shipment_ids[]" value="{{ $s->id }}" class="form-check-input ship-check"
                               data-ship="{{ $s->shipping_cost }}" data-goods="{{ $s->goods_value }}"
                               data-cship="{{ $s->shipping_payer === 'receiver' ? $s->shipping_cost : 0 }}" data-cgoods="{{ $s->payment_method === 'cod' ? $s->goods_value : 0 }}" checked></td>
                    <td>{{ $s->tracking_number }}</td>
                    <td>{{ $s->receiver_name }}<div class="small text-muted">{{ $s->receiver_city }}</div></td>
                    <td>{{ $s->statusLabel() }}</td>
                    <td><span class="badge bg-{{ $s->cod_amount > 0 ? 'warning text-dark' : 'secondary' }}">{{ $s->cod_amount > 0 ? 'COD' : __('No collection') }}</span></td>
                    <td>{{ number_format($s->shipping_cost, 2) }}</td>
                    <td>{{ number_format($s->goods_value, 2) }}</td>
                    <td>{{ number_format($s->total_amount, 2) }}</td>
                    <td class="fw-semibold">{{ number_format($s->cod_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">{{ __('No uninvoiced shipments for this customer') }}</td></tr>
            @endforelse
            </tbody></table></div>
    </div>

    @if($shipments->count())
    <div class="card"><div class="card-body">
        <div class="row g-3">
            @include('partials.input', ['name' => 'discount', 'label' => __('Discount (amount)'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => null, 'placeholder' => '0', 'col' => 4])
            @include('partials.input', ['name' => 'tax_rate', 'label' => __('Tax rate %'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => null, 'placeholder' => '0', 'col' => 4])
            @include('partials.input', ['name' => 'due_date', 'label' => __('Due date'), 'type' => 'date', 'value' => now()->addDays(15)->format('Y-m-d'), 'col' => 4])
            @include('partials.textarea', ['name' => 'notes', 'label' => __('Notes')])
        </div>
        <div class="alert alert-light border mt-3 mb-0 d-flex justify-content-between flex-wrap gap-2">
            <span>{{ __('Shipping fee') }}: <strong id="sShip">0.00</strong></span>
            <span>{{ __('Goods price') }}: <strong id="sGoods">0.00</strong></span>
            <span id="rowSub" class="d-none">{{ __('Subtotal') }}: <strong id="sSub">0.00</strong></span>
            <span id="rowDisc" class="d-none">{{ __('Discount') }}: <strong id="sDisc">0.00</strong></span>
            <span id="rowTax" class="d-none">{{ __('Tax') }}: <strong id="sTax">0.00</strong></span>
            <span>{{ __('Grand total') }}: <strong id="sTotal" class="text-primary fs-5">0.00</strong></span>
        </div>
    </div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4"><i class="bi bi-receipt"></i> {{ __('Issue invoice') }}</button></div></div>
    @endif
</form>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        var checks = document.querySelectorAll('.ship-check');
        if (!checks.length) return;
        function basis() { return document.querySelector('input[name=basis]:checked').value; }
        function show(id, on) { document.getElementById(id).classList.toggle('d-none', !on); }
        function calc() {
            var key = basis(), ship = 0, goods = 0;
            checks.forEach(function (c) {
                if (!c.checked) return;
                if (key === 'collect') { ship += parseFloat(c.dataset.cship) || 0; goods += parseFloat(c.dataset.cgoods) || 0; }
                else if (key === 'total') { ship += parseFloat(c.dataset.ship) || 0; goods += parseFloat(c.dataset.goods) || 0; }
                else { ship += parseFloat(c.dataset.ship) || 0; }
            });
            var sub = ship + goods;
            var disc = Math.min(parseFloat(document.getElementById('f_discount').value) || 0, sub);
            var rate = parseFloat(document.getElementById('f_tax_rate').value) || 0;
            var tax = (sub - disc) * rate / 100;
            document.getElementById('sShip').textContent = ship.toFixed(2);
            document.getElementById('sGoods').textContent = goods.toFixed(2);
            document.getElementById('sSub').textContent = sub.toFixed(2);
            document.getElementById('sDisc').textContent = disc.toFixed(2);
            document.getElementById('sTax').textContent = tax.toFixed(2);
            document.getElementById('sTotal').textContent = (sub - disc + tax).toFixed(2);
            show('rowSub', disc > 0 || rate > 0); show('rowDisc', disc > 0); show('rowTax', rate > 0);
        }
        checks.forEach(function (c) { c.addEventListener('change', calc); });
        document.querySelectorAll('input[name=basis]').forEach(function (r) { r.addEventListener('change', calc); });
        document.getElementById('f_discount').addEventListener('input', calc);
        document.getElementById('f_tax_rate').addEventListener('input', calc);
        document.getElementById('checkAll').addEventListener('change', function (e) {
            checks.forEach(function (c) { c.checked = e.target.checked; }); calc();
        });
        calc();
    })();
</script>
@endpush
