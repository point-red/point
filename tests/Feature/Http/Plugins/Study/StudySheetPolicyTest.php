<?php

namespace Tests\Feature\Http\Plugins\Study;

use App\Model\Plugin\Study\StudySheet;

class StudySheetPolicyTest extends StudyTestCase
{
    /**
     * Test user role permission to see a listing of the resource.
     *
     * @return void
     */
    public function test_index()
    {
        $route = route('study.sheet.index');

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
        $route = route('study.sheet.store');

        $form = [
            'is_draft' => 1,
        ];

        $this->actingAsParent();
        $this->json('post', $route, $form, [$this->headers])->assertSuccessful();

        $this->actingAsAdmin();
        $this->json('post', $route, $form, [$this->headers])->assertSuccessful();
    }
    
    /**
     * Test user role permission to see the specified resource.
     *
     * @return void
     */
    public function test_show()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => factory(\App\Model\Master\User::class),
        ]);

        $route = route('study.sheet.show', ['sheet' => $sheet]);

        $this->actingAsParent();
        $this->json('get', $route, [], [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('get', $route, [], [$this->headers])->assertForbidden();

        // only owner can access the sheet
        $sheet->user_id = $this->parent->id;
        $sheet->save();
        $this->actingAsParent();
        $this->json('get', $route, [], [$this->headers])->assertSuccessful();

        $sheet->user_id = $this->admin->id;
        $sheet->save();
        $this->actingAsAdmin();
        $this->json('get', $route, [], [$this->headers])->assertSuccessful();
    }
    
    /**
     * Test user role permission to update the specified resource in storage.
     *
     * @return void
     */
    public function test_update()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => factory(\App\Model\Master\User::class),
        ]);

        $route = route('study.sheet.show', ['sheet' => $sheet]);

        $form = [
            'is_draft' => 1,
        ];

        $this->actingAsParent();
        $this->json('put', $route, $form, [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('put', $route, $form, [$this->headers])->assertForbidden();

        // only owner can access the sheet
        $sheet->user_id = $this->parent->id;
        $sheet->save();
        $this->actingAsParent();
        $this->json('put', $route, $form, [$this->headers])->assertSuccessful();

        $sheet->user_id = $this->admin->id;
        $sheet->save();
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
        $sheet = factory(StudySheet::class)->create([
            'user_id' => factory(\App\Model\Master\User::class),
        ]);

        $route = route('study.sheet.destroy', ['sheet' => $sheet]);

        $this->actingAsParent();
        $this->json('delete', $route, [], [$this->headers])->assertForbidden();

        $this->actingAsAdmin();
        $this->json('delete', $route, [], [$this->headers])->assertForbidden();

        // only owner can access the sheet
        $sheet->user_id = $this->parent->id;
        $sheet->save();
        $this->actingAsParent();
        $this->json('delete', $route, [], [$this->headers])->assertSuccessful();

        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->admin->id,
        ]);
        $route = route('study.sheet.destroy', ['sheet' => $sheet]);
        $this->actingAsAdmin();
        $this->json('delete', $route, [], [$this->headers])->assertSuccessful();
    }
}
