<?php

namespace Tests\Feature\Http\Plugins\Study;

use App\Model\Plugin\Study\StudySubject;

class StudySubjectRequestTest extends StudyTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setRole();
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
            'name' => __('validation.required', ['attribute' => 'name']),
        ];
        
        // create
        $route = route('study.subject.store');
        $this->json('post', $route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);

        // update
        $subject = factory(StudySubject::class)->create();
        $route = route('study.subject.update', ['subject' => $subject]);
        $this->json('put', $route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is too long.
     *
     * @return void
     */
    public function test_max_length()
    {
        $randomString = \Illuminate\Support\Str::random(500);
        $form = [
            'name' => $randomString,
        ];
        $errors = [
            'name' => __('validation.max.string', ['attribute' => 'name', 'max' => 255]),
        ];

        // create
        $route = route('study.subject.store');
        $this->json('post', $route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
            
        // update
        $subject = factory(StudySubject::class)->create();
        $route = route('study.subject.update', ['subject' => $subject]);
        $this->json('put', $route, $form, [$this->headers])
            ->assertJsonValidationErrors($errors);
    }
}
