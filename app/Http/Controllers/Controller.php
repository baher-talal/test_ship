<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Determines the branch when creating a new record:
     * - A user who works on several branches (or all branches) picks the branch from the list.
     * - Otherwise their single branch is used automatically.
     * Returns null for top management when no branch is chosen (when $required = false).
     */
    protected function resolveBranchId(Request $request, $required = true)
    {
        $user = $request->user();

        if ($user->needsBranchChoice()) {
            $id = $request->input('branch_id');

            if ($id) {
                $allowed = $user->selectableBranches()->pluck('id')->map(function ($i) {
                    return (int) $i;
                })->all();

                if (! in_array((int) $id, $allowed, true)) {
                    throw ValidationException::withMessages(['branch_id' => __('The selected branch is not allowed for you').'.']);
                }

                return (int) $id;
            }

            if ($required || ! $user->canAllBranches()) {
                throw ValidationException::withMessages(['branch_id' => __('Select branch').'.']);
            }

            return null;
        }

        $branchId = $user->defaultBranchId();

        if (! $branchId && $required) {
            throw ValidationException::withMessages(['branch_id' => __('No branch is assigned to your account. Ask the manager to assign one').'.']);
        }

        return $branchId;
    }
}
