<?php

namespace Melbedran\Multira\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Melbedran\Multira\Facades\Multira;

class LocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! Multira::checkLocaleInSupportedLocales($locale)) {
            abort(404);
        }

        session([Multira::sessionKey() => $locale]);

        $referer = url()->previous();

        if ($referer && ! str_contains($referer, '/multira/switch')) {
            return redirect()->to(Multira::getLocalizedURL($locale, $referer));
        }

        return redirect()->to(Multira::getLocalizedURL($locale, '/'));
    }
}
