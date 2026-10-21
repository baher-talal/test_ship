<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Shipment::forUser($user)->with(['customer', 'courier', 'branch']);

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('shipments.tracking_number', 'like', "%{$q}%")
                    ->orWhere('shipments.receiver_name', 'like', "%{$q}%")
                    ->orWhere('shipments.receiver_phone', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }
        if ($status = $request->query('status')) {
            $query->where('shipments.status', $status);
        }
        if ($request->filled('courier_id')) {
            $query->where('shipments.courier_id', $request->query('courier_id'));
        }
        if ($user->needsBranchChoice() && $request->filled('branch_id')) {
            $query->where('shipments.branch_id', $request->query('branch_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('shipments.created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('shipments.created_at', '<=', $request->query('to'));
        }
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $shipments = $query->latest('shipments.id')->paginate(15)->appends($request->query());

        return view('shipments.index', [
            'shipments' => $shipments,
            'couriers' => $user->isCourier() ? collect() : Courier::forUser($user)->orderBy('name')->get(),
            'branches' => $user->needsBranchChoice() ? $user->selectableBranches() : collect(),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        return view('shipments.create', [
            'shipment' => new Shipment(['service_type' => 'standard', 'payment_method' => 'prepaid', 'shipping_payer' => 'sender', 'pickup_type' => 'pickup', 'package_type' => 'parcel', 'weight' => 1, 'pieces' => 1, 'goods_value' => 0, 'total_amount' => 0, 'cod_amount' => 0]),
            'customers' => Customer::forUser($user)->where('is_active', true)->orderBy('name')->get(),
            'couriers' => Courier::forUser($user)->where('is_active', true)->orderBy('name')->get(),
            'branches' => $user->selectableBranches(),
            'sender' => $request->filled('customer_id')
                ? Customer::forUser($user)->find($request->query('customer_id'))
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->validated($request);

        $branchId = $this->resolveBranchId($request);
        $customer = $this->resolveCustomer($data, $branchId);

        $data = Arr::except($data, ['sender_name', 'sender_phone', 'sender_address', 'branch_id']);
        $data['customer_id'] = $customer->id;
        $data['branch_id'] = $branchId;
        $data['created_by'] = $user->id;
        $data['tracking_number'] = Shipment::generateTrackingNumber();
        $data['status'] = 'pending';
        $data['expected_delivery'] = $data['expected_delivery'] ?? Shipment::expectedDateFor($data['service_type'])->toDateString();
        $data = $this->normalizeCod($data);

        if (! empty($data['courier_id'])) {
            $this->assertCourierAllowed($data['courier_id'], $branchId);
        }

        $shipment = Shipment::create($data);
        $shipment->histories()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'location' => $shipment->origin_city,
            'note' => __('Shipment created'),
        ]);

        UserNotification::notify(
            $shipment->stakeholderIds($user->id),
            __('New shipment').' '.$shipment->tracking_number,
            __('From').' '.$customer->name.' '.__('To').' '.$shipment->receiver_city,
            route('shipments.show', $shipment->id),
            'info'
        );

        return redirect()->route('shipments.show', $shipment)->with('success', __('Shipment created with tracking number').' '.$shipment->tracking_number);
    }

    public function show(Request $request, Shipment $shipment)
    {
        $this->authorizeShipment($request->user(), $shipment);
        $shipment->load(['customer', 'courier', 'branch', 'creator', 'histories.user', 'invoice']);

        $couriers = $request->user()->isCourier()
            ? collect()
            : Courier::where('is_active', true)
                ->when($shipment->branch_id, function ($q) use ($shipment) {
                    $q->where('branch_id', $shipment->branch_id);
                })->orderBy('name')->get();

        return view('shipments.show', [
            'shipment' => $shipment,
            'couriers' => $couriers,
            'allowedStatuses' => $this->allowedStatuses($request->user()),
        ]);
    }

    public function edit(Request $request, Shipment $shipment)
    {
        $user = $request->user();
        $this->authorizeShipment($user, $shipment);

        return view('shipments.edit', [
            'shipment' => $shipment,
            'customers' => Customer::forUser($user)->orderBy('name')->get(),
            'couriers' => Courier::forUser($user)->where('is_active', true)->orderBy('name')->get(),
            'branches' => $user->selectableBranches(),
            'sender' => $shipment->customer,
        ]);
    }

    public function update(Request $request, Shipment $shipment)
    {
        $user = $request->user();
        $this->authorizeShipment($user, $shipment);

        $data = $this->validated($request);
        $customer = $this->resolveCustomer($data, $shipment->branch_id);

        $data = Arr::except($data, ['sender_name', 'sender_phone', 'sender_address', 'branch_id']);
        $data['customer_id'] = $customer->id;
        $data = $this->normalizeCod($data);

        if (! empty($data['courier_id'])) {
            $this->assertCourierAllowed($data['courier_id'], $shipment->branch_id);
        }

        $shipment->update($data);

        return redirect()->route('shipments.show', $shipment)->with('success', __('Shipment updated').'.');
    }

    public function destroy(Request $request, Shipment $shipment)
    {
        $this->authorizeShipment($request->user(), $shipment);

        if ($shipment->invoice_id) {
            return back()->with('error', __('A shipment linked to an invoice cannot be deleted').'.');
        }

        $shipment->delete();

        return redirect()->route('shipments.index')->with('success', __('Shipment deleted').'.');
    }

    /** Assign the shipment to a courier */
    public function assign(Request $request, Shipment $shipment)
    {
        $user = $request->user();
        $this->authorizeShipment($user, $shipment);

        $data = $request->validate(['courier_id' => 'required|exists:couriers,id']);
        $courier = $this->assertCourierAllowed($data['courier_id'], $shipment->branch_id);

        $shipment->courier_id = $courier->id;
        $shipment->save();

        $shipment->histories()->create([
            'user_id' => $user->id,
            'status' => $shipment->status,
            'note' => __('The shipment was assigned to courier').' '.$courier->name,
        ]);

        if ($courier->user_id) {
            UserNotification::notify(
                [$courier->user_id],
                __('A new shipment was assigned to you'),
                $shipment->tracking_number.' - '.$shipment->receiver_name.' ('.$shipment->receiver_city.')',
                route('shipments.show', $shipment->id),
                'info'
            );
        }

        return back()->with('success', __('The shipment was assigned to').' '.$courier->name);
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $user = $request->user();
        $this->authorizeShipment($user, $shipment);

        $allowed = array_keys($this->allowedStatuses($user));

        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
            'location' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
        ]);

        if ($data['status'] === $shipment->status) {
            return back()->with('error', __('The shipment is already in this status').'.');
        }

        if ($shipment->isFinal() && ! $user->hasPermission('shipments.final_override')) {
            return back()->with('error', __('The shipment is in a final status and can only be changed by a manager').'.');
        }

        $shipment->changeStatus($data['status'], $user, $data['note'] ?? null, $data['location'] ?? null);

        $this->syncCourierState($shipment);

        return back()->with('success', __('Shipment status updated to').': '.$shipment->statusLabel());
    }

    /** Printable shipping label */
    public function label(Request $request, Shipment $shipment)
    {
        $this->authorizeShipment($request->user(), $shipment);
        $shipment->load(['customer', 'branch', 'courier']);

        return view('shipments.label', compact('shipment'));
    }

    /* ----------------------------- Helpers ----------------------------- */

    protected function validated(Request $request)
    {
        $data = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:30',
            'sender_address' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'courier_id' => 'nullable|exists:couriers,id',
            'origin_city' => 'required|string|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:30',
            'receiver_city' => 'required|string|max:255',
            'receiver_address' => 'required|string|max:255',
            'service_type' => ['required', Rule::in(array_keys(Shipment::SERVICES))],
            'weight' => 'required|numeric|min:0.01|max:100000',
            'pieces' => 'required|integer|min:1|max:10000',
            'description' => 'nullable|string|max:255',
            'shipping_cost' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(array_keys(Shipment::PAYMENTS))],
            'goods_value' => 'nullable|numeric|min:0',
            'shipping_payer' => ['required', Rule::in(array_keys(Shipment::PAYERS))],
            'reference_number' => 'nullable|string|max:100',
            'pickup_type' => ['required', Rule::in(array_keys(Shipment::PICKUP_TYPES))],
            'package_type' => ['required', Rule::in(array_keys(Shipment::PACKAGE_TYPES))],
            'length' => 'nullable|numeric|min:0|max:100000',
            'width' => 'nullable|numeric|min:0|max:100000',
            'height' => 'nullable|numeric|min:0|max:100000',
            'is_fragile' => 'nullable|boolean',
            'allow_open' => 'nullable|boolean',
            'receiver_phone2' => 'nullable|string|max:30',
            'receiver_district' => 'nullable|string|max:255',
            'receiver_landmark' => 'nullable|string|max:255',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        return $data;
    }

    /**
     * The sender is typed as text: look for a customer with the same phone in the branch; if none exists, add it to the customers page automatically.
     */
    protected function resolveCustomer(array $data, $branchId)
    {
        $phone = trim($data['sender_phone']);

        $customer = Customer::where('phone', $phone)
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }, function ($q) {
                $q->whereNull('branch_id');
            })->first();

        if (! $customer) {
            $customer = Customer::create([
                'branch_id' => $branchId,
                'name' => trim($data['sender_name']),
                'phone' => $phone,
                'city' => $data['origin_city'] ?? null,
                'address' => $data['sender_address'] ?? null,
                'is_active' => true,
            ]);
        }

        return $customer;
    }

    /** Calculate the total and the amount to collect from the receiver */
    protected function normalizeCod(array $data)
    {
        $data['goods_value'] = $data['goods_value'] ?? 0;
        $data['is_fragile'] = ! empty($data['is_fragile']);
        $data['allow_open'] = ! empty($data['allow_open']);

        return array_merge($data, Shipment::computeAmounts(
            $data['shipping_cost'], $data['goods_value'], $data['payment_method'], $data['shipping_payer']
        ));
    }

    protected function authorizeShipment(User $user, Shipment $shipment)
    {
        $ok = Shipment::forUser($user)->where('shipments.id', $shipment->id)->exists();
        abort_unless($ok, 403, __('You do not have access to this shipment').'.');
    }

    protected function assertCourierAllowed($courierId, $branchId)
    {
        $courier = Courier::findOrFail($courierId);

        if ($branchId && $courier->branch_id && (int) $courier->branch_id !== (int) $branchId) {
            abort(422, __('The courier does not belong to the shipment branch').'.');
        }

        return $courier;
    }

    protected function allowedStatuses(User $user)
    {
        $all = Shipment::STATUSES;

        if ($user->isCourier()) {
            return array_intersect_key($all, array_flip(['picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'returned']));
        }

        return $all;
    }

    /** Update the courier status (available/busy) automatically */
    protected function syncCourierState(Shipment $shipment)
    {
        $courier = $shipment->courier;
        if (! $courier || $courier->status === 'off') {
            return;
        }

        $hasActive = Shipment::where('courier_id', $courier->id)->active()->exists();
        $courier->update(['status' => $hasActive ? 'busy' : 'available']);
    }
}
