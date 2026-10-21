<?php

namespace App\Models;

/**
 * Restricts data to the branches assigned to the user.
 * Users with the scope.all_branches permission (top management) see every branch.
 */
trait BranchScoped
{
    public function scopeForUser($query, $user)
    {
        if ($user->canAllBranches()) {
            return $query;
        }

        return $query->whereIn($this->getTable().'.branch_id', $user->branchIds() ?: [0]);
    }
}
