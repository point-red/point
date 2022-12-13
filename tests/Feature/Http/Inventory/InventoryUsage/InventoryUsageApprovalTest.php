<?php

namespace Tests\Feature\Http\Inventory\InventoryUsage;

use Tests\TestCase;

use App\Model\SettingJournal;
use App\Model\Master\Item;
use App\Model\Inventory\InventoryUsage\InventoryUsage;

class InventoryUsageApprovalTest extends TestCase
{
    use InventoryUsageSetup;

    public static $path = '/api/v1/inventory/usages';

    private $previousInventoryUsageData;

    /** @test */
    public function success_create_inventory_usage($isFirstCreate = true)
    {
        $data = $this->getDummyData();
        
        if($isFirstCreate) {
            $this->setRole();
            $this->previousInventoryUsageData = $data;
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
    public function iu_af1_unauthorized_approve_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function iu_ef13_invalid_journal_approve_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        SettingJournal::where('feature', 'inventory usage')
            ->where('name', 'difference stock expense')
            ->update([
                "chart_of_account_id" => null,
            ]);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Journal inventory usage account - difference stock expense not found"
            ]);
    }

    /** @test */
    public function success_approve_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $usage = InventoryUsage::orderBy('id', 'asc')->first();
        $usageItem = $usage->items()->first();
        $usageItemAmount = $usageItem->item->cogs($usageItem->item_id) * $usageItem->quantity;

        $approver = $usage->form->requestApprovalTo;
        $this->changeActingAs($approver, $usage);

        $response = $this->json('POST', self::$path . '/' . $usage->id . '/approve', [], $this->headers);
        $response->assertStatus(200);

        // check balance and match amount
        $this->assertDatabaseHas('journals', [
            'form_id' => $usage->form->id,
            'journalable_type' => Item::$morphName,
            'journalable_id' => $usageItem->item_id,
            'chart_of_account_id' => $usageItem->chart_of_account_id,
            'debit' => $usageItemAmount,
        ], 'tenant');
        $this->assertDatabaseHas('journals', [
            'form_id' => $usage->form->id,
            'journalable_type' => Item::$morphName,
            'journalable_id' => $usageItem->item_id,
            'chart_of_account_id' => get_setting_journal('inventory usage', 'difference stock expense'),
            'credit' => $usageItemAmount,
        ], 'tenant');

        // change form status changed and logged
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_by' => $response->json('data.form.approval_by'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Approved'
        ], 'tenant');

        $responseRecap = $this->json('GET', '/api/v1/inventory/inventory-recapitulations', [
            'includes' => 'account',
            'sort_by' => 'code;name',
            'limit' => 10,
            'page' => 1,
            'date_from' => date('Y-m-01') . ' 00:00:00',
            'filter_to' => date('Y-m-31') . ' 23:59:59',
            'filter_like' => '{}',
        ], $this->headers);
        $responseRecap->assertStatus(200)
            ->assertJsonFragment([
                "name" => $usageItem->item->name,
                "stock_in" => number_format((float) $this->initialItemQuantity, 30, '.', ''),
                "stock_out" => number_format((float) $this->initialUsageItemQuantity * -1, 30, '.', ''),
                "ending_balance" => number_format((float) $this->initialItemQuantity + ($this->initialUsageItemQuantity * -1), 30, '.', '')
            ]);
    }

    /** @test */
    public function iu_af2_not_found_form_approve_inventory_usage()
    {
        $this->success_approve_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/approve', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Approval not found"
            ]);
    }

    /** @test */
    public function iu_rf1_invalid_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', [], $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid.",
                "errors" => [
                    "reason" => [
                        "The reason field is required."
                    ]
                ]
            ]);
    }

    /** @test */
    public function iu_rf2_unauthorized_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function success_reject_inventory_usage()
    {
        $this->success_create_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
        $inventoryUsageItem = $inventoryUsage->items()->first();

        $approver = $inventoryUsage->form->requestApprovalTo;
        $this->changeActingAs($approver, $inventoryUsage);

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('journals', [
            'form_id' => $inventoryUsage->form->id,
            'journalable_type' => Item::$morphName,
            'journalable_id' => $inventoryUsageItem->item_id,
            'chart_of_account_id' => $inventoryUsageItem->chart_of_account_id,
        ], 'tenant');

        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'InventoryUsage',
            'activity' => 'Rejected'
        ], 'tenant');

        $responseRecap = $this->json('GET', '/api/v1/inventory/inventory-recapitulations', [
            'includes' => 'account',
            'sort_by' => 'code;name',
            'limit' => 10,
            'page' => 1,
            'date_from' => date('Y-m-01') . ' 00:00:00',
            'filter_to' => date('Y-m-31') . ' 23:59:59',
            'filter_like' => '{}',
        ], $this->headers);
        $responseRecap->assertStatus(200)
            ->assertJsonMissing([
                "stock_out" => number_format((float) $this->initialUsageItemQuantity * -1, 30, '.', ''),
                "ending_balance" => number_format((float) $this->initialItemQuantity + ($this->initialUsageItemQuantity * -1), 30, '.', '')
            ]);
    }

    /** @test */
    public function iu_rf3_not_found_form_reject_inventory_usage()
    {
        $this->success_reject_inventory_usage();

        $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $inventoryUsage->id . '/reject', $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Approval not found"
            ]);
    }
    
    /** @test */
    // public function success_read_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $data = [
    //         'join' => 'form,customer,items,item',
    //         'fields' => 'sales_inventory_usage.*',
    //         'sort_by' => '-form.number',
    //         'group_by' => 'form.id',
    //         'filter_form'=>'notArchived;null',
    //         'filter_like'=>'{}',
    //         'filter_date_min'=>'{"form.date":"2022-05-01 00:00:00"}',
    //         'filter_date_max'=>'{"form.date":"2022-05-17 23:59:59"}',
    //         'includes'=>'form;customer;warehouse;items.item;items.allocation',
    //         'limit'=>10,
    //         'page' => 1
    //     ];

    //     $response = $this->json('GET', self::$path . '/approval', $data, $this->headers);
        
    //     $response->assertStatus(200);
    // }

    /** @test */
    // public function success_send_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $inventoryUsage = InventoryUsage::orderBy('id', 'asc')->first();
    //     $data['ids'][] = ['id' => $inventoryUsage->id];

    //     $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    //     $response->assertStatus(200);
    // }

    /** @test */
    // public function success_send_multiple_approval_inventory_usage()
    // {
    //     $this->success_create_inventory_usage();

    //     $this->success_create_inventory_usage($isFirstCreate = false);
    //     $inventoryUsage = InventoryUsage::orderBy('id', 'desc')->first();
    //     $inventoryUsage->form->request_approval_to = $this->previousInventoryUsageData['request_approval_to'];
    //     $inventoryUsage->form->save();

    //     $data['ids'] = InventoryUsage::get()
    //         ->pluck('id')
    //         ->map(function ($id) { return ['id' => $id]; })
    //         ->toArray();

    //     $response = $this->json('POST', self::$path . '/approval/send', $data, $this->headers);

    //     $response->assertStatus(200);
    // }
}
