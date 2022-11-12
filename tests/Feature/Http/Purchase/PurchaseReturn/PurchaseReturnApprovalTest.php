<?php

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Purchase\PurchaseReturn\PurchaseReturn;

class PurchaseReturnApprovalTest extends TestCase
{
    use PurchaseReturnSetup;

    public static $path = '/api/v1/purchase/return';

    private $previousPurchaseReturnData;

    /** @test */
    public function success_create_purchase_return($isFirstCreate = true)
    {
        $data = $this->getDummyData();
        
        if($isFirstCreate) {
            $this->setRole();
            $this->previousPurchaseReturnData = $data;
        }

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/approve', [], $this->headers);
        $response->assertStatus(200);
        $subTotal = $response->json('data.amount') - $response->json('data.tax');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Approved'
        ], 'tenant');
        $this->assertDatabaseHas('journals', [
            'form_id' => $response->json('data.form.id'),
            'chart_of_account_id' => $this->apCoa->id
        ], 'tenant');
        $this->assertDatabaseHas('journals', [
            'form_id' => $response->json('data.form.id'),
            'chart_of_account_id' => $this->taxCoa->id
        ], 'tenant');
    }

    /** @test */
    public function error_form_already_approved_approve_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $purchaseReturn->form->approval_status = 1;
        $purchaseReturn->form->save();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/approve', [], $this->headers);
        $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Can't approve, form already approved!"
          ]);
    }

    /** @test */
    public function error_unauthorized_approve_purchase_return()
    {
        $this->success_create_purchase_return();

        $this->unsetUserRole();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }

    /** @test */
    public function error_no_reason_reject_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function error_reason_more_than_255_character_reject_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(300);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Reason can\t more than 255 character!"
            ]);
    }

    /** @test */
    public function error_unauthorized_reject_purchase_return()
    {
        $this->success_create_purchase_return();

        $this->unsetUserRole();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }

    /** @test */
    public function success_reject_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Rejected'
        ], 'tenant');
    }

    /** @test */
    public function read_all_purchase_return_approval()
    {

        $this->success_create_purchase_return();
        $purchaseReturn = PurchaseReturn::whereHas('form', function($query){
            $query->whereApprovalStatus(0); 
        })->get();

        $purchaseReturn = $purchaseReturn->sortByDesc(function($q){
            return $q->form->date;
        });

        $response = $this->json('GET', self::$path . '/approval', [
            'limit' => '10',
            'page' => '1',
        ], $this->headers);

        $data = [];
        foreach ($purchaseReturn as $pReturn) {
            $items = [];
            foreach ($pReturn->items as $item) {
                array_push($items, [
                    "id" => $item->id,
                    "purchase_return_id" => $item->purchase_return_id,
                    "purchase_invoice_item_id" => $item->purchase_invoice_item_id,
                    "item_id" => $item->item_id,
                    "quantity" => $item->quantity,
                    "unit" => $item->unit,
                    "converter" => $item->converter,
                ]);
            }
            array_push($data, [
                "id" => $pReturn->id,
                "supplier_id" => $pReturn->supplier_id,
                "supplier_name" => $pReturn->supplier_name,
                "date" => $pReturn->form->date,
                "number" => $pReturn->form->number,
                "notes" => $pReturn->form->notes,
                "items" => $items
            ]);
        };

        $response->assertStatus(200)
            ->assertJson([
                "data" => $data,
                "links" => [
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "total" => count($purchaseReturn)
                ]
            ]);
    }

    /** @test */
    public function success_send_approval_purchase_return()
    {
      $this->success_create_purchase_return();
      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $data = [
          "ids" => [
              "id" => $purchaseReturn->id,
          ],
      ];

      $response = $this->json('POST', self::$path.'/approval/send', $data, $this->headers);
      
      $response->assertStatus(200)
          ->assertJson([
              "input" => [
                  "ids" => [
                      "id" => $memoJournal->id,
                  ]
              ]
          ]);
    }

    /** @test */
    public function error_default_branch_send_approval_purchase_return()
    {
      $this->success_create_purchase_return();
      $this->unsetBranch();

      $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
      $data = [
          "ids" => [
              "id" => $purchaseReturn->id,
          ],
      ];

      $response = $this->json('POST', self::$path.'/approval/send', $data, $this->headers);
      
      $response->assertStatus(422)
        ->assertJson([
            "code" => 422,
            "message" => "please set default branch to create this form"
        ]);
      $this->setBranch();
    }
}