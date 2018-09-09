<?php

namespace App\Http\Middleware;

use App\Model\Project\Project;
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
        if ($request->header('Tenant') && auth()->user()) {
            // Ignore this, because this subdomain is not allowed
            if ($request->header('Tenant') === 'cloud') {
                return $next($request);
            }

            // Permission denied, the project is not owned by that user
            $project = Project::where('code', $request->header('Tenant'))->where('owner_id', auth()->user()->id)->first();
            if (!$project) {
                return $next($request);
            }

            config()->set('database.connections.tenant.database', 'point_'.$request->header('Tenant'));
            DB::connection('tenant')->reconnect();
        }

        return $next($request);
    }
}
