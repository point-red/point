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
            if ($request->header('Tenant') === 'cloud' || $request->header('Tenant') === 'localhost:8080') {
                return $next($request);
            }

            // Permission denied, the project is not owned by that user
            if (auth()->user()) {

                $authUser = auth()->user();
                $request->merge(compact('authUser'));

                $project = Project::leftJoin('project_preferences', 'project_preferences.project_id', '=', 'projects.id')
                    ->where('code', $request->header('Tenant'))
                    ->select('projects.*')
                    ->with('preference')
                    ->first();

                if (! $project) {
                    return $next($request);
                }

                // Update mail configuration on the fly
                $this->updatePreference('mail.driver', $project->preference, 'mail_driver');
                $this->updatePreference('mail.host', $project->preference, 'mail_host');
                $this->updatePreference('mail.username', $project->preference, 'mail_username');
                $this->updatePreference('mail.password', $project->preference, 'mail_password');
                $this->updatePreference('mail.from.name', $project->preference, 'mail_from_name');
                $this->updatePreference('mail.from.address', $project->preference, 'mail_from_address');
                $this->updatePreference('mail.port', $project->preference, 'mail_port');
                $this->updatePreference('mail.encryption', $project->preference, 'mail_encryption');
                $this->updatePreference('mail.secret', $project->preference, 'mail_secret');

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

    private function updatePreference($config, $preference, $key)
    {
        if (optional($preference)->$key !== null) {
            config()->set($config, $preference->$key);
        }
    }
}
