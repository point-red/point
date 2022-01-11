<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Branch as Branch;
use App\User;

class UserInvitationTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testUpdateBranchCentralTest()
    {
        //create branch Central
        $branch = new Branch;
        $branch->id = 1;
        $branch->name = 'Central';
        $branch->save();

        //create dummy user
        $user = factory(User::class)->create();

        $tenantUser = new TenantUser;
        $tenantUser->id = $user->id;
        $tenantUser->name = $user->name;
        $tenantUser->email = $user->email;
        $tenantUser->save();

        $tenant = tenant($user->id);
        
        //ensure the many2many relationship between user and branch is empty
        $this->assertEquals(0,$tenant->branches()->count());
        // Set user acces to branch central
        $tenant->branches()->syncWithoutDetaching($branch);
        // ensure the many2many relationship between user and branch is filled
        $this->assertEquals(1,$tenant->branches()->count());
        // ensure the many2many relationship between user and branch is Central Branch with id 1
        $this->assertTrue($tenant->branches()->first()->is($branch));
        
        // ensure the is_default column in the many2many relation pivot table between user and branch is 0
        $this->assertEquals(0, $tenant->branches()->first()->pivot->is_default);
        // Set as default user to branch central
        $tenant->branches()->updateExistingPivot($branch, [
            'is_default' => true,
        ], false);
        // ensure the is_default column in the many2many relation table between user and branch is 1
        $this->assertEquals(1, $tenant->branches()->first()->pivot->is_default);
    }
}
