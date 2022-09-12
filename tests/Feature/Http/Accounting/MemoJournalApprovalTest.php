<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Accounting\MemoJournal;
use Tests\TestCase;

class MemoJournalApprovalTest extends TestCase
{
    use MemoJournalSetup;
    
    public static $path = '/api/v1/accounting/approval/memo-journals';

    public function createMemoJournalNotApproved()
    {
        sleep(1);
        $data = $this->dummyData();

        $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);
    }

    public function createMemoJournalApproved()
    {
        $data = $this->dummyData();

        $memoJournal = $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->json('data')["id"].'/approve', [
            'id' => $memoJournal->json('data')["id"]
        ], $this->headers);
    }

    /**
     * @test 
     */
    public function read_all_memo_journal_approval()
    {
        $this->setCreatePermission();
        $this->setApprovePermission();

        $this->createMemoJournalNotApproved();
        $this->createMemoJournalNotApproved();
        $this->createMemoJournalNotApproved();
        $this->createMemoJournalApproved();
        $this->createMemoJournalApproved();

        $memoJournalNotApproved = MemoJournal::whereHas('form', function($query){
            $query->whereApprovalStatus(0); 
        })->get();

        $memoJournalNotApproved = $memoJournalNotApproved->sortByDesc(function($q){
            return $q->form->date;
        });

        $response = $this->json('GET', self::$path, [
            'limit' => '10',
            'page' => '1',
        ], $this->headers);

        $data = [];
        foreach ($memoJournalNotApproved as $memoJournal) {
            $items = [];
            foreach ($memoJournal->items as $item) {
                array_push($items, [
                    "id" => $item->id,
                    "memo_journal_id" => $item->memo_journal_id,
                    "chart_of_account_id" => $item->chart_of_account_id,
                    "chart_of_account_name" => $item->chart_of_account_name,
                    "form_id" => $item->form_id,
                    "masterable_id" => $item->masterable_id,
                    "masterable_type" => $item->masterable_type,
                    "debit" => $item->debit,
                    "credit" => $item->credit,
                    "notes" => $item->notes,
                ]);
            }
            array_push($data, [
                "id" => $memoJournal->id,
                "date" => $memoJournal->form->date,
                "number" => $memoJournal->form->number,
                "notes" => $memoJournal->form->notes,
                "items" => $items
            ]);
        };

        $response->assertStatus(200)
            ->assertJson([
                "data" => $data,
                "links" => [
                    "prev" => null,
                    "next" => null
                ],
                "meta" => [
                    "total" => count($memoJournalNotApproved)
                ]
            ]);
    }

    /**
     * @test 
     */
    public function unauthorized_approve_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/approve', [
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
    public function success_approve_memo_journal()
    {
        $this->setApprovePermission();

        $memoJournal = $this->createMemoJournal();

        $this->assertEquals($memoJournal->form->approval_status, 0);

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $items = [];
        foreach ($memoJournal->items as $item) {
            array_push($items, [
                "id" => $item->id,
                "memo_journal_id" => $item->memo_journal_id,
                "chart_of_account_id" => $item->chart_of_account_id,
                "chart_of_account_name" => $item->chart_of_account_name,
                "form_id" => $item->form_id,
                "masterable_id" => $item->masterable_id,
                "masterable_type" => $item->masterable_type,
                "debit" => $item->debit,
                "credit" => $item->credit,
                "notes" => $item->notes,
            ]);
        }
        
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
                        "approval_by" => $this->user->id,
                        "approval_status" => 1,
                    ],
                    "items" => $items
                ]
            ]);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'approval_by' => $this->user->id,
            'approval_status' => 1,
        ], 'tenant');

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
    }

    /**
     * @test 
     */
    public function unauthorized_reject_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/reject', [
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
    public function invalid_reject_memo_journal()
    {
        $this->setApprovePermission();

        $memoJournal = $this->createMemoJournal();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/reject', [], $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /**
     * @test 
     */
    public function success_reject_memo_journal()
    {
        $this->setApprovePermission();

        $memoJournal = $this->createMemoJournal();

        $this->assertEquals($memoJournal->form->approval_status, 0);

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/reject', [
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
                        'approval_by' => $this->user->id,
                        'approval_status' => -1,
                        'approval_reason' => 'some reason'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'approval_by' => $this->user->id,
            'approval_status' => -1,
            'approval_reason' => 'some reason'
        ], 'tenant');
    }

    /** @test */
    public function send_memo_journal_approval()
    {
        $memoJournal = $this->createMemoJournal();

        $data = [
            "ids" => [
                "id" => $memoJournal->id,
            ],
        ];

        $response = $this->json('POST', self::$path.'/send', $data, $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "input" => [
                    "ids" => [
                        "id" => $memoJournal->id,
                    ]
                ]
            ]);
    }
}
