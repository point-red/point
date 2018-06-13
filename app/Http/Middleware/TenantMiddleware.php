<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
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
        if (env('APP_ENV') === 'production') {
            if ($request->header('Tenant')) {
                config()->set('database.connections.tenant.database', 'point_'.$request->header('Tenant'));
                DB::connection('tenant')->reconnect();
            }
        }

        return $next($request);
    }
}
