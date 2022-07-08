<?php

namespace Tests\Feature\Http\Plugins\Study;

use Tests\TestCase;

class StudyTestCase extends TestCase
{
    protected \App\User $admin;
    protected \App\User $parent;
    private \App\Model\Auth\Role $roleParent;
    private \App\Model\Project\Project $project;

    private array $permissions = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setRole();

        $this->admin = $this->user;

        // $this->setPluginAndProject();
        
        $this->seedStudyPermissions();
        $this->createRoleParent();
        $this->giveSuperadminStudyPermission();

        $this->parent = $this->createParent();
    }

    private function setPluginAndProject(): void
    {
        $this->setProject();
        $this->project = \App\Model\Project\Project::first();

        $pluginStudy = \App\Model\Plugin::where('name', 'STUDY')->first();
        $this->project->plugins()->attach($pluginStudy->id, [
            'expired_date' => now()->addYears(10),
        ]);
    }

    private function seedStudyPermissions(): void
    {
        $this->permissions = [
            'menu study',
            'read study sheets',
            'create study sheets',
            'edit study sheets',
            'delete study sheets',
            'read study subjects',
            'create study subjects',
            'edit study subjects',
            'delete study subjects',
        ];
        foreach ($this->permissions as $permission) {
            \App\Model\Auth\Permission::createIfNotExists($permission);
        }
    }

    private function createRoleParent(): void
    {
        $this->roleParent = \App\Model\Auth\Role::createIfNotExists('parent');
        $this->roleParent->givePermissionTo(array_slice($this->permissions, 0, 5));
    }

    private function giveSuperadminStudyPermission(): void
    {
        $roleSuperadmin = \App\Model\Auth\Role::findByName('super admin', 'api');
        $roleSuperadmin->givePermissionTo($this->permissions);
    }
    
    private function createParent(): \App\User
    {
        // create parent
       $parent = factory(\App\User::class)->create();

        // connectTenantUser
        $tenantUser = new \App\Model\Master\User();
        $tenantUser->id = $parent->id;
        $tenantUser->name = $parent->name;
        $tenantUser->email = $parent->email;
        $tenantUser->save();

        // branch
        $this->userBranch($tenantUser);

        // setRole
        $hasRole = new \App\Model\Auth\ModelHasRole();
        $hasRole->role_id = $this->roleParent->id;
        $hasRole->model_type = 'App\Model\Master\User';
        $hasRole->model_id = $parent->id;
        $hasRole->save();

        // set user as project member
        // $this->project->users()->attach($parent->id, [
        //     'expired_date' => now()->addYears(10),
        //         'user_name' => $parent->name,
        //         'user_email' => $parent->email,
        //         'joined' => 1,
        // ]);

        return $parent;
    }
    
    protected function actingAsAdmin(): void
    {
        $this->actingAs($this->admin, 'api');
    }

    protected function actingAsParent(): void
    {
        $this->actingAs($this->parent, 'api');
    }
}