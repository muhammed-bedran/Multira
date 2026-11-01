<?php

namespace Melbedran\Multira\Middleware;

use Closure;
use Illuminate\Http\Request;
use Melbedran\Multira\Facades\Multira;
use Symfony\Component\HttpFoundation\Response;

class MultiraRedirectFilter
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Multira::shouldIgnoreMethod($request->getMethod()) || Multira::shouldIgnoreUrl($request->getPathInfo())) {
            return $next($request);
        }

        $current = Multira::getCurrentLocale();
        $first = $request->segment(1);
        $hasLocalePrefix = is_string($first) && Multira::checkLocaleInSupportedLocales(
            Multira::getInversedLocaleFromMapping($first)
        );

        if (Multira::hideDefaultLocaleInURL() && $hasLocalePrefix && $current === Multira::getDefaultLocale()) {
            return redirect()->to(Multira::getLocalizedURL($current, $request->fullUrl()), 301);
        }

        if (! Multira::hideDefaultLocaleInURL() && ! $hasLocalePrefix) {
            return redirect()->to(Multira::getLocalizedURL($current, $request->fullUrl()), 302);
        }

        return $next($request);
    }
}
