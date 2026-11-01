<?php

namespace Melbedran\Multira\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Melbedran\Multira\Facades\Multira;
use Symfony\Component\HttpFoundation\Response;

class LocaleViewPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $localePath = resource_path('views/'.Multira::getCurrentLocale());

        if (is_dir($localePath)) {
            View::getFinder()->prependLocation($localePath);
        }

        return $next($request);
    }
}
