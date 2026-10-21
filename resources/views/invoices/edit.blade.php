@extends('layouts.app')
@section('title', __('Edit invoice'))
@section('heading', __('Edit invoice').' '.$invoice->invoice_number)
@section('actions')<a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light">{{ __('Back') }}</a>@endsection
@section('content')
<form method="post" action="{{ route('invoices.update', $invoice) }}">
    @csrf @method('PUT')
    <div class="card mb-3"><div class="card-header">{{ __('Invoice items - Customer') }}: {{ $invoice->customer->name }}</div>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>{{ __('Description') }}</th><th style="width:170px">{{ __('Amount') }}</th><th style="width:170px">{{ __('Shipping fee') }}</th><th style="width:90px">{{ __('Remove item') }}</th></tr></thead>
            <tbody>
            @foreach($invoice->items as $it)
                <tr>
                    <td><input type="text" name="items[{{ $it->id }}][description]" value="{{ old('items.'.$it->id.'.description', $it->description) }}" class="form-control" required></td>
                    <td><input type="number" step="0.01" min="0" name="items[{{ $it->id }}][amount]" value="{{ old('items.'.$it->id.'.amount', $it->amount) }}" class="form-control item-amount" required></td>
                    <td><input type="number" step="0.01" min="0" name="items[{{ $it->id }}][shipping_amount]" value="{{ old('items.'.$it->id.'.shipping_amount', $it->shipping_amount) }}" class="form-control item-ship"></td>
                    <td class="text-center"><input type="checkbox" name="items[{{ $it->id }}][remove]" value="1" class="form-check-input item-remove" {{ old('items.'.$it->id.'.remove') ? 'checked' : '' }}></td>
                </tr>
            @endforeach
            <tr class="table-light">
                <td><input type="text" name="new_description" value="{{ old('new_description') }}" class="form-control" placeholder="{{ __('Add a new item (optional): description') }}"></td>
                <td><input type="number" step="0.01" min="0" name="new_amount" value="{{ old('new_amount') }}" class="form-control" placeholder="{{ __('Amount') }}"></td>
                <td><input type="number" step="0.01" min="0" name="new_shipping_amount" value="{{ old('new_shipping_amount') }}" class="form-control" placeholder="{{ __('Shipping fee') }}"></td>
                <td></td>
            </tr>
            </tbody>
        </table></div>
        <div class="card-footer bg-transparent small text-muted">{{ __('Removing an item releases its shipment so it can be invoiced again') }}.</div>
    </div>

    <div class="card"><div class="card-body"><div class="row g-3">
        @include('partials.input', ['name' => 'issue_date', 'label' => __('Issue date'), 'type' => 'date', 'required' => true, 'value' => $invoice->issue_date->format('Y-m-d'), 'col' => 3])
        @include('partials.input', ['name' => 'due_date', 'label' => __('Due date'), 'type' => 'date', 'value' => optional($invoice->due_date)->format('Y-m-d'), 'col' => 3])
        @include('partials.input', ['name' => 'discount', 'label' => __('Discount'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $invoice->discount, 'col' => 3])
        @include('partials.input', ['name' => 'tax_rate', 'label' => __('Tax rate %'), 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $invoice->tax_rate + 0, 'col' => 3])
        @include('partials.select', ['name' => 'status', 'label' => __('Invoice status'), 'required' => true, 'options' => ['unpaid' => __('Unpaid'), 'paid' => __('Paid'), 'cancelled' => __('Cancelled')], 'value' => $invoice->status, 'col' => 3])
        @include('partials.textarea', ['name' => 'notes', 'label' => __('Notes'), 'value' => $invoice->notes])
    </div>
    <div class="alert alert-light border mt-3 mb-0 d-flex justify-content-between flex-wrap gap-2">
        <span>{{ __('Subtotal') }}: <strong id="sSub">0.00</strong></span>
        <span>{{ __('Tax') }}: <strong id="sTax">0.00</strong></span>
        <span>{{ __('Grand total') }}: <strong id="sTotal" class="text-primary fs-5">0.00</strong></span>
    </div></div>
    <div class="card-footer bg-transparent text-end"><button class="btn btn-primary px-4"><i class="bi bi-check-lg"></i> {{ __('Save changes') }}</button></div></div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        var rows = document.querySelectorAll('.item-amount');
        var newAmt = document.querySelector('[name=new_amount]'), newShip = document.querySelector('[name=new_shipping_amount]'), newDesc = document.querySelector('[name=new_description]');
        function calc() {
            var sub = 0;
            rows.forEach(function (a) {
                var tr = a.closest('tr');
                if (!tr.querySelector('.item-remove').checked) sub += (parseFloat(a.value) || 0) + (parseFloat(tr.querySelector('.item-ship').value) || 0);
            });
            if (newDesc.value) sub += (parseFloat(newAmt.value) || 0) + (parseFloat(newShip.value) || 0);
            var disc = Math.min(parseFloat(document.getElementById('f_discount').value) || 0, sub);
            var rate = parseFloat(document.getElementById('f_tax_rate').value) || 0;
            var tax = (sub - disc) * rate / 100;
            document.getElementById('sSub').textContent = sub.toFixed(2);
            document.getElementById('sTax').textContent = tax.toFixed(2);
            document.getElementById('sTotal').textContent = (sub - disc + tax).toFixed(2);
        }
        document.querySelectorAll('.item-amount, .item-remove, #f_discount, #f_tax_rate, [name=new_amount], [name=new_shipping_amount], [name=new_description], .item-ship').forEach(function (el) {
            el.addEventListener('input', calc); el.addEventListener('change', calc);
        });
        calc();
    })();
</script>
@endpush
