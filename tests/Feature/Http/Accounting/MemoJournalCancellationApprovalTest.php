<?php

namespace Tests\Feature\Http\Accounting;

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
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', self::$path . '/' . $memoJournal->id.'/cancellation-approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }

    /**
     * @test 
     */
    public function success_cancellation_approve_memo_journal()
    {
        $this->setApprovePermission();
        $this->setDeletePermission();

        $memoJournal = $this->createMemoJournal();

        $this->json('DELETE', '/api/v1/accounting/memo-journals/'.$memoJournal->id, ['reason' => 'some rason'], [$this->headers]);

        $this->assertEquals($memoJournal->form->cancellation_status, 0);

        $this->json('POST', self::$path . '/' .$memoJournal->id.'/approve', [
            'id' => $memoJournal->id
        ], $this->headers);

        foreach ($memoJournal->items as $memoJournalItem) {
            $this->assertDatabaseHas('journals', [
                'form_id' => $memoJournal->form->id,
                'journalable_type' => $memoJournalItem->masterable_type,
                'journalable_id' => $memoJournalItem->masterable_id,
                'chart_of_account_id' => $memoJournalItem->chart_of_account_id,
                'debit' => $memoJournalItem->debit,
                'credit' => $memoJournalItem->credit
            ], 'tenant');
        }

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $memoJournal->id,
                    "form" => [
                        "id" => $memoJournal->form->id,
                        "date" => $memoJournal->form->date,
                        "number" => $memoJournal->form->number,
                        "id" => $memoJournal->form->id,
                        "notes" => $memoJournal->form->notes,
                        "cancellation_approval_by" => $this->user->id,
                        "cancellation_status" => 1,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'cancellation_approval_by' => $this->user->id,
            'cancellation_status' => 1,
        ], 'tenant');

        foreach ($memoJournal->items as $memoJournalItem) {
            $this->assertDatabaseMissing('journals', [
                'form_id' => $memoJournal->form->id,
                'journalable_type' => $memoJournalItem->masterable_type,
                'journalable_id' => $memoJournalItem->masterable_id,
                'chart_of_account_id' => $memoJournalItem->chart_of_account_id,
                'debit' => $memoJournalItem->debit,
                'credit' => $memoJournalItem->credit
            ], 'tenant');
        }
    }

    /**
     * @test 
     */
    public function unauthorized_cancellation_reject_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [
            'id' => $memoJournal->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(403)
            ->assertJson([
                "code" => 403,
                "message" => "This action is unauthorized."
            ]);
    }

    /**
     * @test 
     */
    public function invalid_cancellation_reject_memo_journal()
    {
        $this->setApprovePermission();

        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [], $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /**
     * @test 
     */
    public function success_cancellation_reject_memo_journal()
    {
        $this->setApprovePermission();
        $this->setDeletePermission();

        $memoJournal = $this->createMemoJournal();

        $this->json('DELETE', '/api/v1/accounting/memo-journals/'.$memoJournal->id, ['reason' => 'some rason'], [$this->headers]);

        $this->assertEquals($memoJournal->form->cancellation_status, 0);

        $response = $this->json('POST', self::$path . '/' .$memoJournal->id.'/cancellation-reject', [
            'id' => $memoJournal->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $memoJournal->id,
                    "form" => [
                        "id" => $memoJournal->form->id,
                        "date" => $memoJournal->form->date,
                        "number" => $memoJournal->form->number,
                        "id" => $memoJournal->form->id,
                        "notes" => $memoJournal->form->notes,
                        'cancellation_approval_by' => $this->user->id,
                        'cancellation_approval_reason' => 'some reason',
                        'cancellation_status' => -1,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'cancellation_approval_by' => $this->user->id,
            'cancellation_approval_reason' => 'some reason',
            'cancellation_status' => -1,
        ], 'tenant');
    }
}
