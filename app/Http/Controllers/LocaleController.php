<?php

namespace App\Http\Controllers;

class LocaleController extends Controller
{
    /** Switch the language (Arabic / English) and remember it in a cookie for one year */
    public function switch($locale)
    {
        abort_unless(in_array($locale, config('app.available_locales'), true), 404);

        return redirect()->back()->withCookie(cookie()->forever('locale', $locale));
    }
}
