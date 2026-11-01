<?php

namespace Melbedran\Multira\Middleware;

use Closure;
use Illuminate\Http\Request;
use Melbedran\Multira\Facades\Multira;
use Symfony\Component\HttpFoundation\Response;

class MultiraRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if ($route && is_string($route->getName())) {
            Multira::setRouteName($route->getName());
        }

        return $next($request);
    }
}
