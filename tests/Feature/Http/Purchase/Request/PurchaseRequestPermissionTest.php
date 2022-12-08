<?php

namespace Tests\Feature\Http\Purchase\Request;

use Tests\TestCase;

class PurchaseRequestPermissionTest extends TestCase
{
    use PurchaseRequestSetup;

    /** @test */
    public function check_false_permission_access()
    {
        $this->setupUser(true, false);
              
        $this->assertFalse($this->tenantUser->hasPermissionTo('menu purchase'));
        $this->assertFalse($this->tenantUser->hasPermissionTo('create purchase request'));
        $this->assertFalse($this->tenantUser->hasPermissionTo('read purchase request'));
        $this->assertFalse($this->tenantUser->hasPermissionTo('update purchase request'));
        $this->assertFalse($this->tenantUser->hasPermissionTo('delete purchase request'));
        $this->assertFalse($this->tenantUser->hasPermissionTo('approve purchase request'));
    }

    /** @test */
    public function check_true_permission_access()
    {
        $this->setupUser(true, true);
              
        $this->assertTrue($this->tenantUser->hasPermissionTo('menu purchase'));
        $this->assertTrue($this->tenantUser->hasPermissionTo('create purchase request'));
        $this->assertTrue($this->tenantUser->hasPermissionTo('read purchase request'));
        $this->assertTrue($this->tenantUser->hasPermissionTo('update purchase request'));
        $this->assertTrue($this->tenantUser->hasPermissionTo('delete purchase request'));
        $this->assertTrue($this->tenantUser->hasPermissionTo('approve purchase request'));
    }

    /** @test */
    public function add_permission_access()
    {
        $this->setupUser(true, false);
              
        $this->assertFalse($this->tenantUser->hasPermissionTo('approve purchase request'));

        //add permission
        $data = [
            "permission_name" => "approve purchase request",
            "role_id" => $this->role->id
        ];

        $response = $this->json('PATCH', '/api/v1/master/roles/'.$this->role->id.'/permissions', $data, $this->headers);
        $response->assertStatus(200);

        $this->assertTrue($this->tenantUser->hasPermissionTo('approve purchase request'));
    }
}