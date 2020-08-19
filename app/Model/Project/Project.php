<?php

namespace App\Model\Project;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Package;
use App\Model\Plugin;
use App\Model\ProjectPreference;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Project extends Model
{
    protected $connection = 'mysql';

    public static $alias = 'project';

    public function generate()
    {
        // Create new database for tenant project
        $dbName = env('DB_DATABASE').'_'.strtolower($this->code);
        Artisan::call('tenant:database:create', ['db_name' => $dbName]);

        // Update tenant database name in configuration
        config()->set('database.connections.tenant.database', $dbName);
        DB::connection('tenant')->reconnect();
        DB::connection('tenant')->beginTransaction();

        // Migrate database
        Artisan::call('tenant:migrate', ['db_name' => $dbName]);

        // Clone user point into their database
        $user = new \App\Model\Master\User;
        $user->id = $this->owner->id;
        $user->name = $this->owner->name;
        $user->first_name = $this->owner->first_name;
        $user->last_name = $this->owner->last_name;
        $user->email = $this->owner->email;
        $user->save();

        $this->is_generated = true;
        $this->save();

        Artisan::call('tenant:seed:first', ['db_name' => $dbName]);

        Excel::import(new ChartOfAccountImport, storage_path('template/chart_of_accounts_manufacture.xlsx'));

        Artisan::call('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);

        DB::connection('tenant')->commit();
    }

    /**
     * Get the owner that owns the project.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The users that belong to the project.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the preference record associated with the project.
     */
    public function preference()
    {
        return $this->hasOne(ProjectPreference::class);
    }

    public function plugins()
    {
        return $this->belongsToMany(Plugin::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
