<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\State\RefreshTenantDatabaseState;

trait RefreshTenantDatabase
{
    use RefreshDatabase  {
        RefreshDatabase::refreshTestDatabase as parentSaveWithHistory;
    }

    /**
     * Refresh a conventional test tenant database.
     *
     * @return void
     */
    protected function refreshTestDatabase()
    {
        if (! RefreshTenantDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
            ]);
            $this->artisan('db:seed', [
                '--database' => 'tenant',
                '--class' => 'TenantDatabaseSeeder',
            ]);
            $this->artisan('db:seed', [
                '--database' => 'tenant',
                '--class' => 'DummyTenantDatabaseSeeder',
            ]);

            RefreshTenantDatabaseState::$migrated = true;
        }

        config()->set('database.default', 'mysql');
        $this->parentSaveWithHistory();
    }
}
