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
        // client domain
        $tenant_domain = explode('.', $request->getHost());

        // client subdomain
        $tenant_subdomain = $tenant_domain[0];

        if (count($tenant_domain) > 3) {
            config()->set('database.connections.tenant.database', 'point_' . $tenant_subdomain);
            DB::connection('tenant')->reconnect();
        }

        return $next($request);
    }
}
