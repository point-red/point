<?php

namespace Tests\Feature\Http\Purchase;

use App\Model\Form;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Purchase\PurchaseOrder\PurchaseOrder;
use App\Model\Purchase\PurchaseOrder\PurchaseOrderItem;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use App\Model\Purchase\PurchaseRequest\PurchaseRequestItem;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

trait PurchaseOrderSetup
{
    private $permissionsSetup = [
        'create purchase request',
    ];

    private $roleSetup = [
        'super admin',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('tenant:seed:dummy', ['db_name' => env('DB_TENANT_DATABASE')]);

        $this->signIn();
        $this->setProject();

        foreach ($this->permissionsSetup as $permission) {
            $this->createPermission($permission);
        }

        foreach ($this->roleSetup as $role) {
            $this->createRole($role);
        }
    }

    protected function createPermission(string $permission)
    {
        $permission = \App\Model\Auth\Permission::createIfNotExists($permission);
        $hasPermission = new \App\Model\Auth\ModelHasPermission();
        $hasPermission->permission_id = $permission->id;
        $hasPermission->model_type = 'App\Model\Master\User';
        $hasPermission->model_id = $this->user->id;
        $hasPermission->save();
    }

    protected function createRole(string $role)
    {
        $role = \App\Model\Auth\Role::createIfNotExists($role);
        $hasRole = new \App\Model\Auth\ModelHasRole();
        $hasRole->role_id = $role->id;
        $hasRole->model_type = 'App\Model\Master\User';
        $hasRole->model_id = $this->user->id;
        $hasRole->save();
    }

    protected function getPurchaseRequest()
    {
        $form = Form::where('formable_type', 'PurchaseRequest')
            ->orderBy('created_at', 'desc')
            ->first();

        return PurchaseRequest::find($form->formable_id);
    }

    protected function getPurchaseOrder()
    {
        $form = Form::where('formable_type', 'PurchaseOrder')
            ->orderBy('created_at', 'desc')
            ->first();

        return PurchaseOrder::find($form->formable_id);
    }
}
