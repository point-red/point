<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Token;
use Tests\TestCase;

class MemoJournalApprovalByEmailTest extends TestCase
{
    use MemoJournalSetup;
    
    public static $path = '/api/v1/accounting/memo-journals';

    private function findOrCreateToken($tenantUser)
    {
        $approverToken = Token::where('user_id', $tenantUser->id)->first();
        if (!$approverToken) {
            $approverToken = new Token();
            $approverToken->user_id = $tenantUser->id;
            $approverToken->token = md5($tenantUser->email.''.now());
            $approverToken->save();
        }

        return $approverToken;
    }
    
    /** @test */
    public function unauthorized_approve_by_email_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $data = [
            'action' => 'approve',
            'approver_id' => $memoJournal->form->request_approval_to,
            'token' => 'invalid token',
            'resource-type' => 'MemoJournal',
            'ids' => [
                ['id' => $memoJournal->id]
            ],
            'crud-type' => 'create'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Approve email failed"
            ]);
    }
    
    /** @test */
    public function success_approve_by_email_memo_journal()
    {
        $memoJournal =  $this->createMemoJournal();

        $this->assertEquals($memoJournal->form->approval_status, 0);

        $approver = $memoJournal->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $data = [
            'action' => 'approve',
            'approver_id' => $memoJournal->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'MemoJournal',
            'ids' => [
                ['id' => $memoJournal->id]
            ],
            'crud-type' => 'create'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        
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
                    [
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

    /** @test */
    public function unauthorized_reject_by_email_memo_journal()
    {
        $memoJournal = $this->createMemoJournal();

        $data = [
            'action' => 'reject',
            'approver_id' => $memoJournal->form->request_approval_to,
            'token' => 'invalid token',
            'resource-type' => 'MemoJournal',
            'ids' => [
                ['id' => $memoJournal->id]
            ],
            'crud-type' => 'create'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);
        
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Reject email failed"
            ]);
    }
    
    /** @test */
    public function success_reject_by_email_memo_journal()
    {   
        $memoJournal = $this->createMemoJournal();

        $this->assertEquals($memoJournal->form->approval_status, 0);

        $approver = $memoJournal->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $data = [
            'action' => 'reject',
            'approver_id' => $memoJournal->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'MemoJournal',
            'ids' => [
                ['id' => $memoJournal->id]
            ],
            'crud-type' => 'create'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);
        
        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "id" => $memoJournal->id,
                        "form" => [
                            "id" => $memoJournal->form->id,
                            "date" => $memoJournal->form->date,
                            "number" => $memoJournal->form->number,
                            "id" => $memoJournal->form->id,
                            "notes" => $memoJournal->form->notes,
                            'approval_by' => $this->user->id,
                            'approval_status' => -1
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('forms', [
            'id' => $memoJournal->form->id,
            'number' => $memoJournal->form->number,
            'approval_by' => $this->user->id,
            'approval_status' => -1
        ], 'tenant');
    }
}
