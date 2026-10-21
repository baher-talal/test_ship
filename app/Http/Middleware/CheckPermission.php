<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Usage: ->middleware('permission:shipments.create') or several permissions (any one is enough):
     * ->middleware('permission:invoices.edit,invoices.delete')
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, __('The account is not active').'.');
        }

        if (! $user->hasAnyPermission($permissions)) {
            abort(403, __('You do not have permission to access this page').'.');
        }

        return $next($request);
    }
}
