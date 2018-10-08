<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
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
        if ($request->header('Tenant')) {
            // Ignore this, because this subdomain is not allowed
            if ($request->header('Tenant') === 'cloud') {
                return $next($request);
            }

            // Permission denied, the project is not owned by that user
            if (auth()->user()) {
                $project = Project::where('code', $request->header('Tenant'))->first();
                if (! $project) {
                    return $next($request);
                }

                $projectUser = ProjectUser::where('project_id', $project->id)->where('user_id', auth()->user()->id);
                if (! $projectUser) {
                    return $next($request);
                }
            }

            config()->set('database.connections.tenant.database', 'point_'.$request->header('Tenant'));
            DB::connection('tenant')->reconnect();
        }

        return $next($request);
    }
}
