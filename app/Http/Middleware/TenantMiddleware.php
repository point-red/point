<?php

namespace App\Http\Middleware;

use App\Model\Project\Project;
use App\Model\Project\ProjectUser;
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
        if ($request->header('Tenant')) {
            // Ignore this, because this subdomain is not allowed
            if ($request->header('Tenant') === 'staging' || $request->header('Tenant') === 'cloud' || $request->header('Tenant') === 'localhost:8080') {
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

                $timezone = $request->header('Timezone');
                if ($request->header('Timezone') && $request->header('Timezone') != null && isset($timezone) && ! empty($timezone) && $timezone != 'undefined') {
                    config()->set('project.timezone', $request->header('Timezone'));
                } else {
                    config()->set('project.timezone', $project->timezone);
                }

                // Update mail configuration on the fly
                if ($project->preference) {
                    config()->set('mail.driver', $project->preference->mail_driver);
                    config()->set('mail.host', $project->preference->mail_host);
                    config()->set('mail.username', empty($project->preference->mail_username) ? null : decrypt($project->preference->mail_username));
                    config()->set('mail.password', empty($project->preference->mail_password) ? null : decrypt($project->preference->mail_password));
                    config()->set('mail.from.name', $project->preference->mail_from_name);
                    config()->set('mail.from.address', $project->preference->mail_from_address);
                    config()->set('mail.port', $project->preference->mail_port);
                    config()->set('mail.encryption', $project->preference->mail_encryption);

                    $mailSecret = empty($project->preference->mail_secret) ? null : decrypt($project->preference->mail_secret);

                    // Mailgun
                    if ($project->preference->mail_driver === 'mailgun') {
                        config()->set('services.mailgun.domain', decrypt($project->preference->mail_domain));
                        config()->set('services.mailgun.secret', $mailSecret);
                    }

                    // Sparkpost
                    if ($project->preference->mail_driver === 'sparkpost') {
                        config()->set('services.sparkpost.secret', $mailSecret);
                    }
                }

                $projectUser = ProjectUser::where('project_id', $project->id)->where('user_id', auth()->user()->id);
                if (! $projectUser) {
                    return $next($request);
                }
            }

            config()->set('database.connections.tenant.database', env('DB_DATABASE', 'point').'_'.$request->header('Tenant'));
            DB::connection('tenant')->reconnect();
        }

        return $next($request);
    }
}
