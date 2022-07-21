<?php

namespace Tests\Feature\Http\Plugins\Study;

use App\Model\Plugin\Study\StudySubject;

class StudySubjectPolicyTest extends StudyTestCase
{
    /**
     * Test user role permission to see a listing of the resource.
     *
     * @return void
     */
    public function test_index()
    {
        $route = route('study.subject.index');

        $this->actingAsParent();
        $this->json('get', $route, [], [$this->headers])->assertSuccessful();

        $this->actingAsAdmin();
        $this->json('get', $route, [], [$this->headers])->assertSuccessful();
    }
    
    /**
     * Test user role permission to store a newly created resource in storage.
     *
     * @return void
     */
    public function test_store()
    {
        $route = route('study.subject.store');

        $form = [
            'name' => 'Math',
        ];

        $this->actingAsParent();
        $this->json('post', $route, $form, [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('post', $route, $form, [$this->headers])->assertSuccessful();
    }
    
    /**
     * Test user role permission to update the specified resource in storage.
     *
     * @return void
     */
    public function test_update()
    {
        $subject = factory(StudySubject::class)->create();

        $route = route('study.subject.update', ['subject' => $subject]);

        $form = [
            'name' => 'Math',
        ];

        $this->actingAsParent();
        $this->json('put', $route, $form, [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('put', $route, $form, [$this->headers])->assertSuccessful();
    }
    
    /**
     * Test user role permission to remove the specified resource from storage.
     *
     * @return void
     */
    public function test_delete()
    {
        $subject = factory(StudySubject::class)->create();

        $route = route('study.subject.destroy', ['subject' => $subject]);

        $this->actingAsParent();
        $this->json('delete', $route, [], [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('delete', $route, [], [$this->headers])->assertSuccessful();
    }
}
