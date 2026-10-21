<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::forUser($request->user())->withCount('shipments');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $customers = $query->latest('id')->paginate(15)->appends($request->query());

        return view('customers.index', compact('customers'));
    }

    public function create(Request $request)
    {
        return view('customers.create', [
            'customer' => new Customer(['is_active' => true]),
            'branches' => $request->user()->selectableBranches(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['branch_id'] = $this->resolveBranchId($request, false);
        $data['is_active'] = $request->boolean('is_active', true);

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)->with('success', __('Customer added successfully').'.');
    }

    public function show(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($request, $customer);

        $shipments = $customer->shipments()->latest('id')->paginate(10);
        $totals = [
            'shipments' => $customer->shipments()->count(),
            'delivered' => $customer->shipments()->where('status', 'delivered')->count(),
            'unpaid' => (float) $customer->invoices()->where('status', 'unpaid')->sum('total'),
        ];

        return view('customers.show', compact('customer', 'shipments', 'totals'));
    }

    public function edit(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($request, $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'branches' => $request->user()->selectableBranches(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($request, $customer);

        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->user()->needsBranchChoice() && $request->filled('branch_id')) {
            $data['branch_id'] = $this->resolveBranchId($request, false);
        }

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', __('Customer updated').'.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($request, $customer);

        if ($customer->shipments()->exists()) {
            return back()->with('error', __('A customer with shipments cannot be deleted. You can deactivate it instead').'.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', __('Customer deleted').'.');
    }

    protected function validated(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    protected function authorizeCustomer(Request $request, Customer $customer)
    {
        abort_unless(
            Customer::forUser($request->user())->where('customers.id', $customer->id)->exists(),
            403,
            __('You do not have access to this customer').'.'
        );
    }
}
