<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Accounting\MemoJournal;
use Tests\TestCase;

class MemoJournalCancellationApprovalTest extends TestCase
{
    use MemoJournalSetup;
    
    public static $path = '/api/v1/accounting/memo-journals';

    /**
     * @test 
     */
    public function unauthorized_cancellation_approve_memo_journal()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $memoJournal->id.'/cancellation-approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /**
     * @test 
     */
    public function success_cancellation_approve_memo_journal()
    {
        $this->setApprovePermission();

        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function unauthorized_cancellation_reject_memo_journal()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'desc')->first();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [
            'id' => $memoJournal->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }

    /**
     * @test 
     */
    public function invalid_cancellation_reject_memo_journal()
    {
        $this->setApprovePermission();

        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'desc')->first();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [], $this->headers);
        
        $response->assertStatus(422);
    }

    /**
     * @test 
     */
    public function success_cancellation_reject_memo_journal()
    {
        $this->setApprovePermission();

        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'desc')->first();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [
            'id' => $memoJournal->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(200);
    }
}
