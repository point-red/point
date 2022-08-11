<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Accounting\MemoJournal;
use Tests\TestCase;

class MemoJournalHistoryTest extends TestCase
{
    use MemoJournalSetup;
    
    public static $path = '/api/v1/accounting/memo-journals';

    /** @test */
    public function read_memo_journal_histories()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $data = [
            'sort_by' => '-user_activity.date',
            'includes' => 'user',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $memoJournal->id . '/histories', $data, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function success_create_memo_journal_history()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();
        $data = [
            "id" => $memoJournal->id,
            "activity" => "Printed"
        ];

        $response = $this->json('POST', self::$path . '/histories', $data, $this->headers);
        
        $response->assertStatus(201);
    }
}
