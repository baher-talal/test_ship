<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;

class SetLocale
{
    /** Language from the "locale" cookie (ar | en), otherwise the default language */
    public function handle($request, Closure $next)
    {
        $locale = $request->cookie('locale');

        if (! in_array($locale, config('app.available_locales'), true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
