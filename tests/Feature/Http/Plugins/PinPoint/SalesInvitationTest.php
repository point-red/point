<?php

namespace Tests\Feature\Http\Plugins\PinPoint;

use App\Model\Form;
use App\Model\Master\Branch;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Package;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Project\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SalesInvitationTest extends TestCase
{
    /**
     * @var int $itemId
     */
    private $itemId;

    /**
     * @var int $branchId
     */
    private $branchId;

    /**
     * @var int $customerId
     */
    private $customerId;
    
    /**
     * @var User $userNonAdmin
     */
    private $userNonAdmin;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->signIn();
        $this->setRole();
        $this->setPermission();
        $this->dummyData();
    }
    
    /**
     * @return void
     */
    private function dataRequest(): array
    {
        return [
            'join'        => 'form',
            'date_from'   => Carbon::now()->subDays(1)->toDateTimeString(),
            'date_to'     => Carbon::now()->toDateTimeString(),
            'fields'      => 'sales_visitation.*',
            'sort_by'     => '-form.date',
            'filter_like' => [],
            'limit'       => '10'
        ];
    }

    /** @test */
    public function testIndex()
    {
        $response   = $this->callEndPoint($this->dataRequest());
        $decodeData = $this->verifyAndDecode($response);

        $this->assertCount(3, $decodeData['data']);
    }

    /** @test */
    public function testIndexWithCustomer()
    {
        $data       = array_merge($this->dataRequest(), ['customer_id' => $this->customerId]);
        $response   = $this->callEndPoint($data);
        $decodeData = $this->verifyAndDecode($response);

        $this->assertCount(3, $decodeData['data']);
    }

    /** @test */
    public function testIndexWithBranch()
    {
        $data       = array_merge($this->dataRequest(), ['branch_id' => $this->branchId]);
        $response   = $this->callEndPoint($data);
        $decodeData = $this->verifyAndDecode($response);

        $this->assertCount(3, $decodeData['data']);
        $this->assertEquals($this->branchId, $decodeData['data'][0]['branch_id']);
    }

    /** @test */
    public function testIndexWithBranchNotFound()
    {
        $data       = array_merge($this->dataRequest(), ['branch_id' => 2]);
        $response   = $this->callEndPoint($data);
        $decodeData = $this->verifyAndDecode($response);

        $this->assertEmpty($decodeData['data']);
    }

    /** @test */
    public function testIndexWithPaymentMethod()
    {
        $paymentMethod = 'cash';
        $data          = array_merge($this->dataRequest(), ['payment_method' => $paymentMethod]);
        $response      = $this->callEndPoint($data);
        $decodeData    = $this->verifyAndDecode($response);

        $this->assertCount(1, $decodeData['data']);
        $this->assertEquals($paymentMethod, $decodeData['data'][0]['payment_method']);
    }

    /** @test */
    public function testIndexWithPaymentMethodNotFound()
    {
        $data          = array_merge($this->dataRequest(), ['payment_method' => 'sell-out']);
        $response      = $this->callEndPoint($data);
        $decodeData    = $this->verifyAndDecode($response);

        $this->assertEmpty($decodeData['data']);
    }

    /** @test */
    public function testIndexWithSelectedItem()
    {
        $data         = array_merge($this->dataRequest(), ['item_id' => $this->itemId]);
        $response     = $this->callEndPoint($data);
        $decodeData   = $this->verifyAndDecode($response);

        $this->assertCount(2, $decodeData['data']);
        $this->assertEquals($this->itemId, $decodeData['data'][0]['details'][0]['item_id']);
    }

    /** @test */
    public function testIndexWithSelectedItemNotFound()
    {
        $data         = array_merge($this->dataRequest(), ['item_id' => 2]);
        $response     = $this->callEndPoint($data);
        $decodeData   = $this->verifyAndDecode($response);

        $this->assertEmpty($decodeData['data']);
    }

    /** @test */
    public function testIndexWithItemSold()
    {
        $data         = array_merge($this->dataRequest(), ['item_sold' => 'item_sold']);
        $response     = $this->callEndPoint($data);
        $decodeData   = $this->verifyAndDecode($response);

        $this->assertCount(2, $decodeData['data']);
    }

    /** @test */
    public function testIndexWithNoItemSold()
    {
        $data         = array_merge($this->dataRequest(), ['item_sold' => 'no_item_sold']);
        $response     = $this->callEndPoint($data);
        $decodeData   = $this->verifyAndDecode($response);

        $this->assertCount(1, $decodeData['data']);
    }

    /** @test */
    public function testIndexWithNonAdmin()
    {
        $this->dummyUserRolePermission();

        $response   = $this->callEndPoint($this->dataRequest());
        $decodeData = $this->verifyAndDecode($response);

        $this->assertCount(0, $decodeData['data']);
    }

    /**
     * @param  array $requestData
     * @return TestResponse
     */
    private function callEndPoint($requestData = []): TestResponse
    {
        return $this->json(
            'GET',
            '/api/v1/plugin/pin-point/sales-visitation-forms',
            $requestData,
            ['Tenant' => 'dev']
        );
    }

    /**
     * @param TestResponse $response
     * @param integer      $statusCode
     *
     * @return array
     */
    private function verifyAndDecode(TestResponse $response, int $statusCode = 200): array
    {
        $responseContent = $response->getContent();
        $decodedData     = json_decode($responseContent, true);

        $response->assertStatus($statusCode);
        $this->assertJson($responseContent);

        return $decodedData;
    }
    
    /**
     * @return void
     */
    private function dummyData(): void
    {
        $this->dummyProject(); 

        $form     = $this->dummyForm();
        $branch   = $this->dummyBranch();
        $item     = factory(Item::class)->create();
        $customer = factory(Customer::class)->create();

        //set id for test
        $this->itemId     = $item->id;
        $this->branchId   = $branch->id;
        $this->customerId = $customer->id;

        foreach (['cash', 'taking-order', 'credit'] as $payment) {
            $salesVisitation                 = new SalesVisitation();
            $salesVisitation->form_id        = $form->id;
            $salesVisitation->customer_id    = $customer->id;
            $salesVisitation->branch_id      = $branch->id;
            $salesVisitation->payment_method = $payment;
            $salesVisitation->save();

            if ($payment != 'taking-order') {
                $salesVisitationDetail                      = new SalesVisitationDetail();
                $salesVisitationDetail->sales_visitation_id = $salesVisitation->id;
                $salesVisitationDetail->item_id             = $item->id;
                $salesVisitationDetail->save();
            }
        }
    }
    
    /**
     * @return Form
     */
    private function dummyForm(): Form
    {
        $form             = new Form();
        $form->date       = Carbon::now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        return $form;
    }

    /**
     * @return Branch
     */
    private function dummyBranch(): Branch
    {
        $branch       = new Branch();
        $branch->name = 'Central';
        $branch->save();

        return $branch;
    }
    
    /**
     * @return void
     */
    private function dummyProject(): void
    {
        $project             = new Project();
        $project->code       = 'dev';
        $project->owner_id   = $this->user->id;
        $project->package_id = $this->dummyPackage()->id;
        $project->save();
    }
    
    /**
     * @return Package
     */
    private function dummyPackage(): Package
    {
        $package            = new Package();
        $package->code      = 'P001';
        $package->name      = 'Package 1';
        $package->is_active = 1;
        $package->save();

        return $package;
    }

    private function dummyUserRolePermission()
    {
        $this->userNonAdmin = factory(User::class)->create();

        $this->actingAs($this->userNonAdmin, 'api');

        $tenantUser        = new \App\Model\Master\User();
        $tenantUser->id    = $this->userNonAdmin->id;
        $tenantUser->name  = $this->userNonAdmin->name;
        $tenantUser->email = $this->userNonAdmin->email;
        $tenantUser->save();

        // set role
        $role                = \App\Model\Auth\Role::createIfNotExists('non admin');
        $hasRole             = new \App\Model\Auth\ModelHasRole();
        $hasRole->role_id    = $role->id;
        $hasRole->model_type = 'App\Model\Master\User';
        $hasRole->model_id   = $this->userNonAdmin->id;
        $hasRole->save();

        // set permission
        $permission                   = \App\Model\Auth\Permission::createIfNotExists('no permission');
        $hasPermission                = new \App\Model\Auth\ModelHasPermission();
        $hasPermission->permission_id = $permission->id;
        $hasPermission->model_type    = 'App\Model\Master\User';
        $hasPermission->model_id      = $this->userNonAdmin->id;
        $hasPermission->save();
    }
}
