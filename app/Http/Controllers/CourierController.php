<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $query = Courier::forUser($request->user())->with('branch')
            ->withCount(['shipments as active_shipments_count' => function ($q) {
                $q->whereNotIn('status', \App\Models\Shipment::FINAL_STATUSES);
            }]);

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('vehicle_number', 'like', "%{$q}%");
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $couriers = $query->latest('id')->paginate(15)->appends($request->query());

        return view('couriers.index', compact('couriers'));
    }

    public function create(Request $request)
    {
        return view('couriers.create', [
            'courier' => new Courier(['status' => 'available', 'vehicle_type' => 'motorcycle', 'is_active' => true]),
            'branches' => $request->user()->selectableBranches(),
            'users' => $this->availableUsers($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['branch_id'] = $this->resolveBranchId($request, false);
        $data['is_active'] = $request->boolean('is_active', true);

        Courier::create($data);

        return redirect()->route('couriers.index')->with('success', __('Courier added').'.');
    }

    public function show(Request $request, Courier $courier)
    {
        $this->authorizeCourier($request, $courier);

        $shipments = $courier->shipments()->with('customer')->latest('id')->paginate(10);
        $stats = [
            'total' => $courier->shipments()->count(),
            'delivered' => $courier->shipments()->where('status', 'delivered')->count(),
            'active' => $courier->shipments()->active()->count(),
            'returned' => $courier->shipments()->where('status', 'returned')->count(),
        ];

        return view('couriers.show', compact('courier', 'shipments', 'stats'));
    }

    public function edit(Request $request, Courier $courier)
    {
        $this->authorizeCourier($request, $courier);

        return view('couriers.edit', [
            'courier' => $courier,
            'branches' => $request->user()->selectableBranches(),
            'users' => $this->availableUsers($request->user(), $courier),
        ]);
    }

    public function update(Request $request, Courier $courier)
    {
        $this->authorizeCourier($request, $courier);

        $data = $this->validated($request, $courier);
        $data['is_active'] = $request->boolean('is_active');
        if ($request->user()->needsBranchChoice() && $request->filled('branch_id')) {
            $data['branch_id'] = $this->resolveBranchId($request, false);
        }

        $courier->update($data);

        return redirect()->route('couriers.show', $courier)->with('success', __('Courier updated').'.');
    }

    public function destroy(Request $request, Courier $courier)
    {
        $this->authorizeCourier($request, $courier);

        if ($courier->shipments()->active()->exists()) {
            return back()->with('error', __('A courier with active shipments cannot be deleted').'.');
        }

        $courier->delete();

        return redirect()->route('couriers.index')->with('success', __('Courier deleted').'.');
    }

    protected function validated(Request $request, Courier $courier = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'national_id' => 'nullable|string|max:30',
            'vehicle_type' => ['required', Rule::in(array_keys(Courier::VEHICLES))],
            'vehicle_number' => 'nullable|string|max:30',
            'status' => ['required', Rule::in(array_keys(Courier::STATUSES))],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('couriers', 'user_id')->ignore(optional($courier)->id)],
        ]);
    }

    /** User accounts with the "Courier" role that are not linked to another courier */
    protected function availableUsers(User $user, Courier $current = null)
    {
        return User::whereHas('roles', function ($r) {
                $r->where('roles.slug', 'courier');
            })
            ->when(! $user->canAllBranches(), function ($q) use ($user) {
                $q->whereHas('branches', function ($b) use ($user) {
                    $b->whereIn('branches.id', $user->branchIds() ?: [0]);
                });
            })
            ->where(function ($q) use ($current) {
                $q->whereDoesntHave('courier');
                if ($current && $current->user_id) {
                    $q->orWhere('id', $current->user_id);
                }
            })->orderBy('name')->get();
    }

    protected function authorizeCourier(Request $request, Courier $courier)
    {
        abort_unless(
            Courier::forUser($request->user())->where('couriers.id', $courier->id)->exists(),
            403,
            __('You do not have access to this courier').'.'
        );
    }
}
