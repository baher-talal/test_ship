<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users', 'couriers', 'customers', 'shipments'])->orderBy('name')->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create', ['branch' => new Branch(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        Branch::create($data);

        return redirect()->route('branches.index')->with('success', __('Branch added').'.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $this->validated($request, $branch);
        $data['is_active'] = $request->boolean('is_active');
        $branch->update($data);

        return redirect()->route('branches.index')->with('success', __('Branch updated').'.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->shipments()->exists() || $branch->users()->exists()) {
            return back()->with('error', __('A branch with users or shipments cannot be deleted. You can deactivate it instead').'.');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', __('Branch deleted').'.');
    }

    protected function validated(Request $request, Branch $branch = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore(optional($branch)->id)],
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);
    }
}
