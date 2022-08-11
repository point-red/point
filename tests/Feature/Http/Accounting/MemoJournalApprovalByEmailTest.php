<?php

namespace Tests\Feature\Http\Accounting;

use App\Model\Accounting\MemoJournal;
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
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

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
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }
    
    /** @test */
    public function success_approve_by_email_memo_journal()
    {
        $this->setApprovePermission();

        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

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
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_reject_by_email_memo_journal()
    {
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

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
        
        $response->assertStatus(500)
            ->assertJson([
                "code" => 500,
                "message" => "Internal Server Error"
            ]);
    }
    
    /** @test */
    public function success_reject_by_email_memo_journal()
    {
        $this->setApprovePermission();
        
        $this->createMemoJournal();

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

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
        $response->assertStatus(200);
    }
}
