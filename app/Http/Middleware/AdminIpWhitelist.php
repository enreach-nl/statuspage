<?php

namespace CachetHQ\Cachet\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!in_array($request->ip(), explode(',', config('application.ip_whitelist'))))
            throw new HttpException(403);

        return $next($request);
    }
}
