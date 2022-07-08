<?php

namespace Tests\Feature\Http\Plugins\Study;

use App\Model\Plugin\Study\StudySubject;

class StudySubjectControllerTest extends StudyTestCase
{    
    /**
     * Test StudySubjectController@store should return list of existing data.
     * Result should be ordered by name ascending
     * and wrapped in attribute `data`.
     *
     * @return void
     */
    public function test_index()
    {
        $subjects = [
            factory(StudySubject::class)->create(['name' => 'Math']),
            factory(StudySubject::class)->create(['name' => 'English']),
            factory(StudySubject::class)->create(['name' => 'Biology']),
            factory(StudySubject::class)->create(['name' => 'Physics']),
            factory(StudySubject::class)->create(['name' => 'Chemistry']),
        ];

        $route = route('study.subject.index');
        
        $this->json('get', $route, [], [$this->headers])->assertJson([
            'data' => [
                ['id' => $subjects[2]->id], // Biology
                ['id' => $subjects[4]->id], // Chemistry
                ['id' => $subjects[1]->id], // English
                ['id' => $subjects[0]->id], // Math
                ['id' => $subjects[3]->id], // Physics
            ],
        ]);
    }
    
    /**
     * Test StudySubjectController@store should save new data.
     *
     * @return void
     */
    public function test_store()
    {
        $route = route('study.subject.store');
        
        $form = [
            'name' => 'Math',
        ];
        
        $this->json('post', $route, $form, [$this->headers])->assertCreated();

        $this->assertDatabaseHas('study_subjects', [
            'name' => $form['name'],
        ], 'tenant');
    }
    
    /**
     * Test StudySubjectController@update should update existing data.
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
        
        $this->json('put', $route, $form, [$this->headers])->assertSuccessful();

        $this->assertDatabaseHas('study_subjects', [
            'id' => $subject->id,
            'name' => $form['name'],
        ], 'tenant');
    }
    
    /**
     * Test StudySubjectController@destroy should delete existing data.
     *
     * @return void
     */
    public function test_destroy()
    {
        $subject = factory(StudySubject::class)->create();
        
        $route = route('study.subject.destroy', ['subject' => $subject]);
        
        $this->json('delete', $route, [], [$this->headers])->assertSuccessful();

        $this->assertDatabaseMissing('study_subjects', [
            'id' => $subject->id,
        ], 'tenant');
    }
}
