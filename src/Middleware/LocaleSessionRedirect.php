<?php

namespace Melbedran\Multira\Middleware;

use Closure;
use Illuminate\Http\Request;
use Melbedran\Multira\Facades\Multira;
use Symfony\Component\HttpFoundation\Response;

class LocaleSessionRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Multira::shouldIgnoreMethod($request->getMethod()) || Multira::shouldIgnoreUrl($request->getPathInfo())) {
            return $next($request);
        }

        $first = $request->segment(1);
        $localeFromUrl = is_string($first)
            ? Multira::getInversedLocaleFromMapping($first)
            : null;

        if ($localeFromUrl && Multira::checkLocaleInSupportedLocales($localeFromUrl)) {
            $request->session()->put(Multira::sessionKey(), $localeFromUrl);

            return $next($request);
        }

        $sessionLocale = $request->session()->get(Multira::sessionKey());

        if (
            is_string($sessionLocale)
            && Multira::checkLocaleInSupportedLocales($sessionLocale)
            && ! Multira::isHiddenDefault($sessionLocale)
        ) {
            return redirect()->to(Multira::getLocalizedURL($sessionLocale, $request->fullUrl()));
        }

        return $next($request);
    }
}
