<?php

namespace Tests\Feature\Http\Accounting;

use Tests\TestCase;

class MemoJournalHistoryTest extends TestCase
{
    use MemoJournalSetup;
    
    public static $path = '/api/v1/accounting/memo-journals';

    /** @test */
    public function read_memo_journal_histories()
    {
        $memoJournal = $this->createMemoJournal();

        $data_history = [
            "id" => $memoJournal->id,
            "activity" => "Created"
        ];

        $response = $this->json('POST', self::$path . '/histories', $data_history, $this->headers);

        $data = [
            'sort_by' => '-user_activity.date',
            'includes' => 'user',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $memoJournal->id . '/histories', $data, $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "table_type" => "forms",
                        "table_id" => $memoJournal->form->id,
                        "number" => $memoJournal->form->number,
                        "user_id" => $this->user->id,
                        "activity" => "Created",
                    ]
                ]
            ]);
    }

    /** @test */
    public function success_create_memo_journal_history()
    {
        $memoJournal = $this->createMemoJournal();

        $data = [
            "id" => $memoJournal->id,
            "activity" => "Printed"
        ];

        $response = $this->json('POST', self::$path . '/histories', $data, $this->headers);
        
        $response->assertStatus(201)
        ->assertJson([
            "data" => [
                "table_type" => "forms",
                "table_id" => $memoJournal->form->id,
                "number" => $memoJournal->form->number,
                "user_id" => $this->user->id,
                "activity" => "Printed",
            ]
            
        ]);

        $this->assertDatabaseHas('user_activities', [
            'number' => $memoJournal->form->number,
            'table_id' => $memoJournal->form->id,
            'table_type' => "forms",
            "user_id" => $this->user->id,
            'activity' => 'Printed'
        ], 'tenant');
    }
}
