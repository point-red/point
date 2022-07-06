<?php

namespace Tests\Feature\Http\Plugins\Study;

use App\Model\Master\User;
use App\Model\Plugin\Study\StudySheet;
use App\Model\Plugin\Study\StudySubject;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudySheetControllerTest extends TestCase
{
    use WithFaker;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setRole();
    }

    /**
     * Test StudySheetController@index should return list of existing data.
     * Result should be wrapped in attribute `data`
     * and has these attributes.
     *
     * @return void
     */
    public function test_index_attributes()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);

        $route = route('study.sheet.index');

        $this->json('get', $route, [], [$this->headers])->assertJson([
            'data' => [
                [
                    'id' => $sheet->id,
                    'started_at' => $sheet->started_at,
                    'ended_at' => $sheet->ended_at,
                    'subject_id' => $sheet->subject_id,
                    'subject' => [
                        'id' => $sheet->subject->id,
                        'name' => $sheet->subject->name,
                    ],
                    'institution' => $sheet->institution,
                    'teacher' => $sheet->teacher,
                    'competency' => $sheet->competency,
                    'learning_goals' => $sheet->learning_goals,
                    'activities' => $sheet->activities,
                    'grade' => $sheet->grade,
                    'behavior' => $sheet->behavior,
                    'remarks' => $sheet->remarks,
                    'is_draft' => $sheet->is_draft,
                    'created_at' => $sheet->created_at,
                    'updated_at' => $sheet->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Test StudySheetController@index should return list of existing data.
     * Result should be ordered by newest first.
     *
     * @return void
     */
    public function test_index_order_by()
    {
        $sheet1 = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
            'started_at' => today(),
        ]);
        $sheet2 = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
            'started_at' => today()->addHours(10),
        ]);
        $sheet3 = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
            'started_at' => today()->subHours(5),
        ]);

        $route = route('study.sheet.index');

        $this->json('get', $route, ['sort_by' => '-started_at'], [$this->headers])->assertJson([
            'data' => [
                ['id' => $sheet2->id],
                ['id' => $sheet1->id],
                ['id' => $sheet3->id],
            ],
        ]);
    }

    /**
     * Test StudySheetController@index should return list of existing data.
     * Result should exclude sheet created by other user.
     *
     * @return void
     */
    public function test_index_only_own_data()
    {
        $sheets = factory(StudySheet::class, 3)->create([
            'user_id' => $this->user->id,
        ]);
        factory(StudySheet::class, 5)->create([
            'user_id' => factory(User::class),
        ]);

        $route = route('study.sheet.index');

        $this->json('get', $route, ['sort_by' => '-id'], [$this->headers])->assertJson([
            'data' => [
                ['id' => $sheets[2]->id],
                ['id' => $sheets[1]->id],
                ['id' => $sheets[0]->id],
            ],
        ]);
    }

    /**
     * Test StudySheetController@index to get only sheet with status draft.
     * 
     * @return void
     */
    public function test_index_only_draft()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);
        $draft = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
            'is_draft' => true,
        ]);

        $route = route('study.sheet.index');

        $this->json('get', $route, ['filter_equal' => '1'], [$this->headers])
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $draft->id],
                ],
            ]);
    }

    /**
     * Test StudySheetController@index to get draft except draft.
     * 
     * @return void
     */
    public function test_index_except_draft()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);
        $draft = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
            'is_draft' => true,
        ]);

        $route = route('study.sheet.index');

        $this->json('get', $route, ['filter_equal' => '0'], [$this->headers])
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $sheet->id],
                ],
            ]);
    }

    // Order by newest
    // Exclude sheet created by other user
    // Filter by date
    // Filter by subject
    // Filter by Competency
    // Filter by Teacher
    // Filter by Search

    /**
     * Test StudySheetController@show should return these attributes.
     * 
     * @return void
     */
    public function test_show()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);
        $route = route('study.sheet.show', ['sheet' => $sheet]);
        $this->json('get', $route, [], [$this->headers])->assertJson([
            ''
        ]);
    }
    
    /**
     * Test StudySheetController@store should save new data.
     *
     * @return void
     */
    public function test_store()
    {
        $route = route('study.sheet.store');

        $subject = factory(StudySubject::class)->create();
        
        $form = [
            'started_at' => now()->startOfHour(),
            'ended_at' => now()->addHour()->startOfHour(),
            'subject_id' => $subject->id,
            'institution' => $this->faker()->text(),
            'teacher' => $this->faker()->name(),
            'competency' => $this->faker()->text(),
            'learning_goals' => $this->faker()->text(),
            'activities' => $this->faker()->text(),
            'grade' => $this->faker()->numberBetween(0,100),
            'behavior' => 'A',
            'remarks' => $this->faker()->text(),
        ];
        
        $this->json('post', $route, $form, [$this->headers])->assertCreated();

        $this->assertDatabaseHas('study_sheets', [
            'started_at' => $form['started_at'],
            'ended_at' => $form['ended_at'],
            'subject_id' => $form['subject_id'],
            'institution' => $form['institution'],
            'teacher' => $form['teacher'],
            'competency' => $form['competency'],
            'learning_goals' => $form['learning_goals'],
            'activities' => $form['activities'],
            'grade' => $form['grade'],
            'behavior' => $form['behavior'],
            'remarks' => $form['remarks'],
            'user_id' => $this->user->id,
        ], 'tenant');
    }
    
    /**
     * Test StudySheetController@update should update existing data.
     *
     * @return void
     */
    public function test_update()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);
        
        $route = route('study.sheet.update', ['sheet' => $sheet]);

        $subject = factory(StudySubject::class)->create();
        
        $form = [
            'started_at' => now()->startOfHour(),
            'ended_at' => now()->addHour()->startOfHour(),
            'subject_id' => $subject->id,
            'institution' => $this->faker()->text(180),
            'teacher' => $this->faker()->name(),
            'competency' => $this->faker()->text(180),
            'learning_goals' => $this->faker()->text(180),
            'activities' => $this->faker()->text(180),
            'grade' => $this->faker()->numberBetween(0,100),
            'behavior' => 'A',
            'remarks' => $this->faker()->text(180),
        ];
        
        $this->json('put', $route, $form, [$this->headers])->assertSuccessful();

        $this->assertDatabaseHas('study_sheets', [
            'id' => $sheet->id,
            'started_at' => $form['started_at']->format('Y-m-d H:i:s'),
            'ended_at' => $form['ended_at']->format('Y-m-d H:i:s'),
            'subject_id' => $form['subject_id'],
            'institution' => $form['institution'],
            'teacher' => $form['teacher'],
            'competency' => $form['competency'],
            'learning_goals' => $form['learning_goals'],
            'activities' => $form['activities'],
            'grade' => $form['grade'],
            'behavior' => $form['behavior'],
            'remarks' => $form['remarks'],
            'user_id' => $this->user->id,
        ], 'tenant');
    }
    
    /**
     * Test StudySheetController@destroy should delete existing data.
     *
     * @return void
     */
    public function test_destroy()
    {
        $sheet = factory(StudySheet::class)->create([
            'user_id' => $this->user->id,
        ]);

        $route = route('study.sheet.update', ['sheet' => $sheet]);

        $this->json('delete', $route, [], [$this->headers])->assertSuccessful();

        $this->assertDeleted('study_sheets', [
            'id' => $sheet->id,
        ], 'tenant');
    }

    // Store photo
    // Store voice
    // Store video

    // Update photo
    // Update voice
    // Update video

    // Delete photo
    // Delete voice
    // Delete video
}
