<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::forUser($request->user())->with('customer');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%");
                    });
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->latest('id')->paginate(15)->appends($request->query());
        $summary = [
            'unpaid' => (float) Invoice::forUser($request->user())->where('status', 'unpaid')->sum('total'),
            'paid' => (float) Invoice::forUser($request->user())->where('status', 'paid')->sum('total'),
        ];

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $customers = Customer::forUser($user)->orderBy('name')->get();
        $customer = null;
        $shipments = collect();

        if ($request->filled('customer_id')) {
            $customer = Customer::forUser($user)->findOrFail($request->query('customer_id'));
            $shipments = $this->billableShipments($user, $customer);
        }

        return view('invoices.create', [
            'customers' => $customers,
            'customer' => $customer,
            'shipments' => $shipments,
            'taxRate' => config('shipping.tax_rate'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'basis' => 'required|in:shipping,total,collect',
            'shipment_ids' => 'required|array|min:1',
            'shipment_ids.*' => 'integer',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ], ['shipment_ids.required' => __('Select at least one shipment to issue the invoice').'.']);

        $customer = Customer::forUser($user)->findOrFail($data['customer_id']);
        $shipments = $this->billableShipments($user, $customer)->whereIn('id', $data['shipment_ids']);

        // One line per shipment (goods price); the shipping fee is kept on the line but shown as a summary row before the total due
        $lines = [];
        foreach ($shipments as $shipment) {
            $lines[$shipment->id] = $this->linesFor($shipment, $data['basis']);
        }
        $shipments = $shipments->filter(function ($shipment) use ($lines) {
            return count($lines[$shipment->id]) > 0;
        });

        if ($shipments->isEmpty()) {
            return back()->withInput()->with('error', __('The selected shipments cannot be invoiced').'.');
        }

        $invoice = DB::transaction(function () use ($data, $customer, $shipments, $lines, $user) {
            $subtotal = 0;
            foreach ($shipments as $shipment) {
                foreach ($lines[$shipment->id] as $line) {
                    $subtotal += $line['amount'] + $line['shipping_amount'];
                }
            }
            $subtotal = round($subtotal, 2);
            $discount = min((float) ($data['discount'] ?? 0), $subtotal);
            $taxRate = (float) ($data['tax_rate'] ?? 0);
            $taxAmount = round(($subtotal - $discount) * $taxRate / 100, 2);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'branch_id' => $customer->branch_id ?: $user->branch_id,
                'customer_id' => $customer->id,
                'created_by' => $user->id,
                'issue_date' => Carbon::today(),
                'due_date' => $data['due_date'] ?? Carbon::today()->addDays(15),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => round($subtotal - $discount + $taxAmount, 2),
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($shipments as $shipment) {
                foreach ($lines[$shipment->id] as $line) {
                    $invoice->items()->create($line + ['shipment_id' => $shipment->id]);
                }
                $shipment->update(['invoice_id' => $invoice->id]);
            }

            return $invoice;
        });

        UserNotification::notify(
            User::whereHas('roles', function ($r) {
                $r->where('roles.slug', 'admin');
            })->where('id', '!=', $user->id)->pluck('id'),
            __('Invoice issued').' '.$invoice->invoice_number,
            __('Customer').': '.$customer->name.' - '.__('Total').': '.number_format($invoice->total, 2),
            route('invoices.show', $invoice->id),
            'success'
        );

        return redirect()->route('invoices.show', $invoice)->with('success', __('The invoice was issued successfully').'.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->load(['customer', 'items.shipment', 'branch', 'creator']);

        return view('invoices.show', compact('invoice'));
    }

    public function printView(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->load(['customer', 'items', 'branch']);

        return view('invoices.print', compact('invoice'));
    }

    /** Edit an issued invoice (general manager / branch manager) */
    public function edit(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->load(['customer', 'items.shipment']);

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Invoice::STATUSES))],
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.shipping_amount' => 'nullable|numeric|min:0',
            'items.*.remove' => 'nullable',
            'new_description' => 'nullable|string|max:255',
            'new_amount' => 'nullable|numeric|min:0',
            'new_shipping_amount' => 'nullable|numeric|min:0',
        ]);

        $existing = $invoice->items()->get()->keyBy('id');
        $keep = 0;
        foreach ($data['items'] as $id => $row) {
            if ($existing->has($id) && empty($row['remove'])) {
                $keep++;
            }
        }
        $addsNew = ! empty($data['new_description']) && (isset($data['new_amount']) || isset($data['new_shipping_amount']));
        if ($keep + ($addsNew ? 1 : 0) < 1) {
            return back()->withInput()->with('error', __('The invoice must contain at least one item. To remove the whole invoice use the delete button').'.');
        }

        DB::transaction(function () use ($invoice, $data, $existing, $addsNew) {
            foreach ($data['items'] as $id => $row) {
                $item = $existing->get($id);
                if (! $item) {
                    continue;
                }
                if (! empty($row['remove'])) {
                    if ($item->shipment_id) {
                        Shipment::where('id', $item->shipment_id)->where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
                    }
                    $item->delete();
                } else {
                    $item->update([
                        'description' => $row['description'],
                        'amount' => $row['amount'],
                        'shipping_amount' => $row['shipping_amount'] ?? 0,
                    ]);
                }
            }

            if ($addsNew) {
                $invoice->items()->create([
                    'kind' => 'other',
                    'description' => $data['new_description'],
                    'amount' => $data['new_amount'] ?? 0,
                    'shipping_amount' => $data['new_shipping_amount'] ?? 0,
                ]);
            }

            $subtotal = round((float) $invoice->items()->sum('amount') + (float) $invoice->items()->sum('shipping_amount'), 2);
            $discount = min((float) ($data['discount'] ?? 0), $subtotal);
            $taxRate = (float) ($data['tax_rate'] ?? 0);
            $taxAmount = round(($subtotal - $discount) * $taxRate / 100, 2);

            $paidAt = $invoice->paid_at;
            if ($data['status'] === 'paid' && ! $paidAt) {
                $paidAt = Carbon::now();
            } elseif ($data['status'] !== 'paid') {
                $paidAt = null;
            }

            $invoice->update([
                'status' => $data['status'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total' => round($subtotal - $discount + $taxAmount, 2),
                'paid_at' => $paidAt,
                'notes' => $data['notes'] ?? null,
            ]);

            // Link/release shipments according to the invoice status
            $shipmentIds = $invoice->items()->whereNotNull('shipment_id')->pluck('shipment_id');
            if ($data['status'] === 'cancelled') {
                Shipment::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
            } else {
                Shipment::whereIn('id', $shipmentIds)->whereNull('invoice_id')->update(['invoice_id' => $invoice->id]);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', __('The invoice was updated').'.');
    }

    /** Delete an issued invoice (general manager / branch manager) and release its shipments */
    public function destroy(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);

        DB::transaction(function () use ($invoice) {
            Shipment::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
            $invoice->delete(); // items are deleted automatically (cascade)
        });

        return redirect()->route('invoices.index')->with('success', __('The invoice was deleted and its shipments released for re-invoicing').'.');
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);

        if ($invoice->status !== 'unpaid') {
            return back()->with('error', __('The payment of this invoice cannot be recorded').'.');
        }

        $invoice->update(['status' => 'paid', 'paid_at' => Carbon::now()]);

        return back()->with('success', __('The invoice payment was recorded').'.');
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);

        if ($invoice->status !== 'unpaid') {
            return back()->with('error', __('Only unpaid invoices can be cancelled').'.');
        }

        DB::transaction(function () use ($invoice) {
            Shipment::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
            $invoice->update(['status' => 'cancelled']);
        });

        return back()->with('success', __('The invoice was cancelled and its shipments released').'.');
    }

    /**
     * Invoicing basis: shipping = shipping fee only | total = shipping + goods price | collect = amount to collect from the receiver
     */
    /**
     * Invoice line for one shipment. The shipping fee is kept in its own field so it can be
     * shown as a summary row before the total due instead of as a line item:
     *  - shipping: shipping fee only
     *  - total:    shipping fee + goods price
     *  - collect:  what is collected from the receiver (goods price if COD, shipping fee if paid by the receiver)
     */
    protected function linesFor(Shipment $s, $basis)
    {
        $shipping = (float) $s->shipping_cost;
        $goods = (float) $s->goods_value;

        if ($basis === 'collect') {
            $shipping = $s->shipping_payer === 'receiver' ? $shipping : 0;
            $goods = $s->payment_method === 'cod' ? $goods : 0;
        } elseif ($basis === 'shipping') {
            $goods = 0;
        }

        if ($shipping <= 0 && $goods <= 0) {
            return [];
        }

        return [[
            'kind' => 'shipment',
            'description' => $s->tracking_number.' - '.$s->receiver_city,
            'amount' => $goods,
            'shipping_amount' => $shipping,
        ]];
    }

    protected function billableShipments(User $user, Customer $customer)
    {
        return Shipment::forUser($user)
            ->where('shipments.customer_id', $customer->id)
            ->whereNull('shipments.invoice_id')
            ->where('shipments.status', '!=', 'cancelled')
            ->orderBy('shipments.id')
            ->get();
    }

    protected function authorizeInvoice(Request $request, Invoice $invoice)
    {
        abort_unless(
            Invoice::forUser($request->user())->where('invoices.id', $invoice->id)->exists(),
            403,
            __('You do not have access to this invoice').'.'
        );
    }
}
