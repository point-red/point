<?php

namespace Tests;

use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\ChartOfAccountType;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;
    /**
     * Database migration can be so slow on local machine
     * Instead run migrate on each test, you can run manually
     * "php artisan migrate:fresh --env=testing"
     * "php artisan migrate:fresh --env=testing --database=tenant --path=database/migrations/tenant"
     * and comment this code below "use RefreshTenantDatabase;"
     * and uncomment "use DatabaseTransactions;".
     *
     * By default we still use "use RefreshTenantDatabase;" for integration with travis, etc
     * So you shouldn't commit this change
     */
    use RefreshTenantDatabase;
    // use DatabaseTransactions;

    // Setting this allows both DB connections to be reset between tests
    protected $connectionsToTransact = ['mysql', 'tenant'];

    protected $user;
    protected $account = null;

    /**
     *  Set up the test.
     */
    public function setUp(): void
    {
        parent::setUp();

        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        \DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        \DB::rollback();

        $this->logRequestTime();

        parent::tearDown();
    }

    protected function signIn()
    {
        $this->user = factory(User::class)->create();

        $this->actingAs($this->user, 'api');

        $this->connectTenantUser();
    }

    protected function connectTenantUser()
    {
        $tenantUser = new \App\Model\Master\User();
        $tenantUser->id = $this->user->id;
        $tenantUser->name = $this->user->name;
        $tenantUser->email = $this->user->email;
        $tenantUser->save();
    }

    protected function logRequestTime()
    {
        $start = LARAVEL_START;
        $end = microtime(true);
        $diff = $end - $start;
        Log::channel('testing')->info('['.app('request')->method().'] '
            .app('request')->url()
            .' '
            .$diff);
    }

    protected function createSampleChartAccountType()
    {
        $chartOfAccountType = new ChartOfAccountType();
        $chartOfAccountType->name = 'Test Type';
        $chartOfAccountType->alias = 'Testing one';
        $chartOfAccountType->is_debit = true;
        $chartOfAccountType->save();

        $this->createSampleChartAccount($chartOfAccountType);
    }

    protected function createSampleChartAccount($chartOfAccountType)
    {
        $chartOfAccount = new ChartOfAccount();
        $chartOfAccount->type_id = $chartOfAccountType->id;
        $chartOfAccount->number = 'TEST01';
        $chartOfAccount->name = 'Testing Chart';
        $chartOfAccount->alias = 'Testing chart';
        $chartOfAccount->save();
        $this->account = $chartOfAccount;
    }
}
