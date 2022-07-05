<?php

namespace Tests\Feature\Http\Plugins\Study;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudySheetStoreRequestTest extends TestCase
{
    use WithFaker;

    private string $route;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setRole();

        $this->route = route('study.sheet.store');
    }

    /**
     * Test error message when field is not provided or empty.
     *
     * @return void
     */
    public function test_required()
    {
        $form = [];
        $errors = [
            'started_at' => __('validation.required', ['attribute' => 'started at']),
            'ended_at' => __('validation.required', ['attribute' => 'ended at']),
            'subject_id' => __('validation.required', ['attribute' => 'subject id']),
            'competency' => __('validation.required', ['attribute' => 'competency']),
            'learning_goals' => __('validation.required', ['attribute' => 'learning goals']),
            'behavior' => __('validation.required', ['attribute' => 'behavior']),
            'is_draft' => __('validation.required', ['attribute' => 'is draft']),
        ];
        
        $this->json('post', $this->route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not exist in database.
     *
     * @return void
     */
    public function testExist()
    {
        $form = [
            'subject_id' => 0,
        ];
        $errors = [
            'subject_id' => __('validation.exists', ['attribute' => 'subject id']),
        ];
        $this->json('post', $this->route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not a string.
     *
     * @return void
     */
    public function testString()
    {
        $form = [
            'competency' => ['key' => 'value'],
            'learning_goals' => ['key' => 'value'],
            'behavior' => ['key' => 'value'],
        ];
        $errors = [
            'competency' => __('validation.string', ['attribute' => 'competency']),
            'learning_goals' => __('validation.string', ['attribute' => 'learning goals']),
            'behavior' => __('validation.string', ['attribute' => 'behavior']),
        ];
        $this->json('post', $this->route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
    }
}
