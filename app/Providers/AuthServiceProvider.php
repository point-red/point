<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Model\Plugin\Study\StudySubject' => 'App\Policies\Plugin\Study\StudySubjectPolicy',
        'App\Model\Plugin\Study\StudySheet' => 'App\Policies\Plugin\Study\StudySheetPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Route::prefix('api/v1')->group(function () {
            Passport::routes();
        });
    }
}
