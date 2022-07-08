<?php

namespace Tests\Feature\Http\Plugins\Study;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;

class StudySheetStoreRequestTest extends StudyTestCase
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
     * Call the given URI with a JSON POST request.
     */
    private function jsonPost(array $form)
    {
        return $this->json('post', $this->route, $form, [$this->headers]);
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
            'is_draft' => __('validation.required', ['attribute' => 'is draft']),
        ];
        
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not provided or empty.
     *
     * @return void
     */
    public function test_required_if_not_draft()
    {
        $form = [
            'is_draft' => false,
        ];
        $errors = [
            'started_at' => __('validation.required_if', [
                'attribute' => 'started at',
                'other' => 'is draft',
                'value' => 'false'
            ]),
            'ended_at' => __('validation.required_if', [
                'attribute' => 'ended at',
                'other' => 'is draft',
                'value' => 'false'
            ]),
            'subject_id' => __('validation.required_if', [
                'attribute' => 'subject id',
                'other' => 'is draft',
                'value' => 'false'
            ]),
            'competency' => __('validation.required_if', [
                'attribute' => 'competency',
                'other' => 'is draft',
                'value' => 'false'
            ]),
            'learning_goals' => __('validation.required_if', [
                'attribute' => 'learning goals',
                'other' => 'is draft',
                'value' => 'false'
            ]),
            'behavior' => __('validation.required_if', [
                'attribute' => 'behavior',
                'other' => 'is draft',
                'value' => 'false'
            ]),
        ];
        
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not exist in database.
     *
     * @return void
     */
    public function test_exist()
    {
        $form = [
            'subject_id' => 0,
        ];
        $errors = [
            'subject_id' => __('validation.exists', ['attribute' => 'subject id']),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not a string.
     *
     * @return void
     */
    public function test_string()
    {
        $form = [
            'institution' => ['key' => 'value'],
            'teacher' => ['key' => 'value'],
            'competency' => ['key' => 'value'],
            'learning_goals' => ['key' => 'value'],
            'behavior' => ['key' => 'value'],
            'activities' => ['key' => 'value'],
            'remarks' => ['key' => 'value'],
        ];
        $errors = [
            'institution' => __('validation.string', ['attribute' => 'institution']),
            'teacher' => __('validation.string', ['attribute' => 'teacher']),
            'competency' => __('validation.string', ['attribute' => 'competency']),
            'learning_goals' => __('validation.string', ['attribute' => 'learning goals']),
            'behavior' => __('validation.string', ['attribute' => 'behavior']),
            'activities' => __('validation.string', ['attribute' => 'activities']),
            'remarks' => __('validation.string', ['attribute' => 'remarks']),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
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
            'field' => $randomString,
            'institution' => $randomString,
            'teacher' => $randomString,
            'competency' => $randomString,
            'learning_goals' => $randomString,
            'behavior' => $randomString,
            'activities' => $randomString,
            'remarks' => $randomString,
        ];
        $errors = [
            'institution' =>  __('validation.max.string', ['attribute' => 'institution', 'max' => 180]),
            'teacher' =>  __('validation.max.string', ['attribute' => 'teacher', 'max' => 180]),
            'competency' =>  __('validation.max.string', ['attribute' => 'competency', 'max' => 180]),
            'learning_goals' =>  __('validation.max.string', ['attribute' => 'learning goals', 'max' => 180]),
            'behavior' =>  __('validation.max.string', ['attribute' => 'behavior', 'max' => 1]),
            'activities' =>  __('validation.max.string', ['attribute' => 'activities', 'max' => 180]),
            'remarks' =>  __('validation.max.string', ['attribute' => 'remarks', 'max' => 180]),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is valid enum.
     *
     * @return void
     */
    public function test_enum()
    {
        $form = [
            'behavior' => 'some text',
        ];
        $errors = [
            'behavior' => __('validation.in', [
                'attribute' => 'behavior',
            ]),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not an integer.
     *
     * @return void
     */
    public function test_integer()
    {
        $form = [
            'grade' => 'some string',
        ];
        $errors = [
            'grade' => __('validation.integer', ['attribute' => 'grade']),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is above the maximum value.
     *
     * @return void
     */
    public function test_max()
    {
        $form = [
            'grade' => 500,
        ];
        $errors = [
            'grade' => __('validation.max.numeric', [
                'attribute' => 'grade',
                'max' => 100
            ]),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test error message when field is not a boolean.
     *
     * @return void
     */
    public function test_boolean()
    {
        $errors = [
            'is_draft' => __('validation.boolean', ['attribute' => 'is draft']),
        ];

        $form = [
            'is_draft' => 'some text',
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);

        $form = [
            'is_draft' => 5,
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);

        $form = [
            'is_draft' => 0,
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'is_draft',
        ]);

        $form = [
            'is_draft' => 1,
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'is_draft',
        ]);

        $form = [
            'is_draft' => '0',
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'is_draft',
        ]);

        $form = [
            'is_draft' => '1',
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'is_draft',
        ]);
    }

    /**
     * Test error message when field is not an image.
     *
     * @return void
     */
    public function test_file()
    {
        $form = [
            'photo' => UploadedFile::fake()->create('not image.php', 2, 'php'),
            'audio' => UploadedFile::fake()->create('not audio.php', 2, 'php'),
            'video' => UploadedFile::fake()->create('not video.php', 2, 'php'),
        ];
        $errors = [
            'photo' => __('validation.image', ['attribute' => 'photo']),
            'audio' => __('validation.mimetypes', [
                'attribute' => 'audio',
                'values' => 'audio/*'
            ]),
            'video' => __('validation.mimetypes', [
                'attribute' => 'video',
                'values' => 'video/*'
            ]),
        ];
        $this->jsonPost($form)->assertJsonValidationErrors($errors);
    }

    /**
     * Test optional parameter should not error when empty or not provided.
     *
     * @return void
     */
    public function test_optional()
    {
        // not provided
        $form = [];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'photo',
            'audio',
            'video',
            'institution',
            'teacher',
            'activities',
            'grade',
            'remarks',
        ]);
    
        // provided null
        $form = [
            'photo' => null,
            'audio' => null,
            'video' => null,
            'institution' => null,
            'teacher' => null,
            'activities' => null,
            'grade' => null,
            'remarks' => null,
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'photo',
            'audio',
            'video',
            'institution',
            'teacher',
            'activities',
            'grade',
            'remarks',
        ]);
    
        // provided empty
        $form = [
            'photo' => '',
            'audio' => '',
            'video' => '',
            'institution' => '',
            'teacher' => '',
            'activities' => '',
            'grade' => '',
            'remarks' => '',
        ];
        $this->jsonPost($form)->assertJsonMissingValidationErrors([
            'photo',
            'audio',
            'video',
            'institution',
            'teacher',
            'activities',
            'grade',
            'remarks',
        ]);
    }
}
