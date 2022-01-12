<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Branch as Branch;
use App\User;

class UserInvitationByRequestJoinTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testUpdateBranchCentralTest()
    {
        
        $branch = new Branch;
        $branch->id = 1;
        $branch->name = 'Central';
        $branch->save();

        $user = factory(User::class)->create();

        $tenantUser = new TenantUser;
        $tenantUser->id = $user->id;
        $tenantUser->name = $user->name;
        $tenantUser->email = $user->email;
        $tenantUser->save();

        $tenant = tenant($user->id);
        
        $this->assertEquals(0,$tenant->branches()->count());
        // Set user acces to branch central
        $tenant->branches()->syncWithoutDetaching($branch);

        $this->assertEquals(1,$tenant->branches()->count());
        
        $this->assertTrue($tenant->branches()->first()->is($branch));

        $this->assertEquals(0, $tenant->branches()->first()->pivot->is_default);
        
        // Set as default user to branch central
        $tenant->branches()->updateExistingPivot($branch, [
            'is_default' => true,
        ], false);

        $this->assertEquals(1, $tenant->branches()->first()->pivot->is_default);
    }
}
