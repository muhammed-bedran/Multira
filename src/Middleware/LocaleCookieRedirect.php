<?php

namespace Melbedran\Multira\Middleware;

use Closure;
use Illuminate\Http\Request;
use Melbedran\Multira\Facades\Multira;
use Symfony\Component\HttpFoundation\Response;

class LocaleCookieRedirect
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
            $response = $next($request);

            return $response->withCookie(cookie(
                Multira::cookieName(),
                $localeFromUrl,
                Multira::cookieMinutes()
            ));
        }

        $cookieLocale = $request->cookie(Multira::cookieName());

        if (
            is_string($cookieLocale)
            && Multira::checkLocaleInSupportedLocales($cookieLocale)
            && ! Multira::isHiddenDefault($cookieLocale)
        ) {
            return redirect()->to(Multira::getLocalizedURL($cookieLocale, $request->fullUrl()));
        }

        return $next($request);
    }
}
