<?php 

namespace Tests\Feature\Http\Sales\PaymentCollection;

use App\Model\Auth\Role;
use App\Model\Auth\ModelHasRole;
use App\Model\Form;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesDownPayment\SalesDownPayment;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Master\Allocation;
use App\Model\Master\Branch;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Customer;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\Payment\PaymentDetail;
use App\Model\Master\Warehouse;

trait PaymentCollectionSetup {
    private $tenantUser;
    private $defaultBranch;
    private $customer;
    private $approver;
    private $warehouseSelected;

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();

        $this->tenantUser = TenantUser::find($this->user->id);
        $this->defaultBranch = $this->tenantUser->branches()
                ->where('is_default', true)
                ->first();
        $this->setUserWarehouse($this->defaultBranch);
        $_SERVER['HTTP_REFERER'] = 'http://www.example.com/';
    }
    
    private function setUserWarehouse($branch = null)
    {
        $warehouse = $this->createWarehouse($branch);
        $this->tenantUser->warehouses()->syncWithoutDetaching($warehouse->id);
        foreach ($this->tenantUser->warehouses as $warehouse) {
            $warehouse->pivot->is_default = true;
            $warehouse->pivot->save();
    
            $this->warehouseSelected = $warehouse;
        }
    }

    private function createWarehouse($branch = null)
    {
        $warehouse = new Warehouse();
        $warehouse->name = 'Test warehouse';

        if($branch) $warehouse->branch_id = $branch->id;

        $warehouse->save();

        return $warehouse;
    }

    public function createChartOfAccount()
    {
        $chartOfAccountTypeDebit = ChartOfAccountType::where('is_debit', 1)->first();
        
        if (!$chartOfAccountTypeDebit) {
            $chartOfAccountTypeDebit = new ChartOfAccountType;
            $chartOfAccountTypeDebit->name = 'OTHER EXPENSE';
            $chartOfAccountTypeDebit->alias = 'BEBAN NON OPERASIONAL';
            $chartOfAccountTypeDebit->is_debit = 1;

            $chartOfAccountTypeDebit->save();
        }

        $chartOfAccountTypeCredit = ChartOfAccountType::where('is_debit', 0)->first();
        if(!$chartOfAccountTypeCredit) {
            $chartOfAccountTypeCredit = new ChartOfAccountType;
            $chartOfAccountTypeCredit->name = 'OTHER INCOME';
            $chartOfAccountTypeCredit->alias = 'PENDAPATAN LAIN-LAIN';
            $chartOfAccountTypeCredit->is_debit = 0;

            $chartOfAccountTypeCredit->save();
        }        

        $chartOfAccountCredit = ChartOfAccount::where('position', 'CREDIT')->first();
        if(!$chartOfAccountCredit) {
            $chartOfAccountCredit = new ChartOfAccount;
            $chartOfAccountCredit->type_id = $chartOfAccountTypeCredit->id;
            $chartOfAccountCredit->position = 'CREDIT';
            $chartOfAccountCredit->number = '41106';
            $chartOfAccountCredit->name = 'OTHER INCOME';
            $chartOfAccountCredit->alias = 'PENDAPATAN LAIN-LAIN';
            $chartOfAccountCredit->created_by = $this->tenantUser->id;
            $chartOfAccountCredit->updated_by = $this->tenantUser->id;

            $chartOfAccountCredit->save();
        }

        $chartOfAccountDebit = ChartOfAccount::where('position', 'DEBIT')->first();
        if (!$chartOfAccountDebit) {
            $chartOfAccountDebit = new ChartOfAccount;
            $chartOfAccountDebit->type_id = $chartOfAccountTypeDebit->id;
            $chartOfAccountDebit->position = 'DEBIT';
            $chartOfAccountDebit->number = '51107';
            $chartOfAccountDebit->name = 'OFFICE ADMINISTRATION EXPENSE';
            $chartOfAccountDebit->alias = 'ADMINISTRASI BANK';
            $chartOfAccountDebit->created_by = $this->tenantUser->id;
            $chartOfAccountDebit->updated_by = $this->tenantUser->id;

            $chartOfAccountDebit->save();
        }        
    }

    public function createForm($formableType, $formableId, $prefix) {
        if (!$this->approver) {
            $role = Role::createIfNotExists('super admin');
            $this->approver = factory(TenantUser::class)->create();
            $this->approver->assignRole($role);
        }

        $branch = Branch::orderBy('id', 'asc')->first();
        if (!$branch) {
            $branch = $this->createBranch();
        }

        $defaultNumberPostfix = '{y}{m}{increment=4}';

        $lastForm = Form::where('formable_type', $formableType)
                ->whereNotNull('number')
                ->where('increment_group', date("Ym"))
                ->orderBy('increment', 'desc')
                ->first();

        $form = new Form;
        $form->branch_id =  $branch->id;
        $form->date = date("Y-m-d H:i:s");
        $form->increment_group = date("Ym");
        $form->notes = "some notes";
        $form->created_by = $this->tenantUser->id;
        $form->updated_by = $this->tenantUser->id;
        $form->formable_id = $formableId;
        $form->formable_type = $formableType;
	    $form->generateFormNumber(
	        $prefix.$defaultNumberPostfix,
	        $this->customer->id,
	        0
	    );
        $form->request_approval_to = $this->approver->id;
        $form->approval_by = $this->approver->id;
        $form->approval_at = now();
        $form->approval_status = 1;
        $form->save();        
        
    }
    
    public function createSalesInvoice()
    {
        if (!$this->customer) {
            $this->customer = factory(Customer::class)->create();
        }
        $salesInvoice = new SalesInvoice;
        $salesInvoice->customer_id = $this->customer->id;
        $salesInvoice->customer_name = $this->customer->name;
        $salesInvoice->amount = 2000000;

        $salesInvoice->save();
        $this->createForm('SalesInvoice', $salesInvoice->id, 'SI');
    }

    public function createSalesReturn()
    {
        if (!$this->customer) {
            $this->customer = factory(Customer::class)->create();
        }

        $salesInvoice  = SalesInvoice::orderBy('id', 'asc')->first();

        $salesReturn = new SalesReturn;
        $salesReturn->sales_invoice_id = $salesInvoice->id;
        $salesReturn->customer_id = $this->customer->id;
        $salesReturn->customer_name = $this->customer->name;
        $salesReturn->amount = 200000;

        $salesReturn->save();
        $this->createForm('SalesReturn', $salesReturn->id, 'SR');
    }

    public function createDownPayment()
    {
        if (!$this->customer) {
            $this->customer = factory(Customer::class)->create();
        }

        $salesDownPayment = new SalesDownPayment;
        $salesDownPayment->customer_id = $this->customer->id;
        $salesDownPayment->customer_name = $this->customer->name;
        $salesDownPayment->amount = 500000;

        $salesDownPayment->save();

        $this->createForm('SalesDownPayment', $salesDownPayment->id, 'DP');
    }

    public function getDummy() {
        $this->createChartOfAccount();
        $this->createSalesInvoice();
        $this->createSalesReturn();
        $this->createDownPayment();

        $salesInvoice  = SalesInvoice::orderBy('id', 'desc')->first();
        $salesReturn  = SalesReturn::orderBy('id', 'desc')->first();
        $salesDownPayment  = SalesDownPayment::orderBy('id', 'desc')->first();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => $this->customer->id,
            "customer_name" => $this->customer->name,
            "payment_type" => "cash",
            "request_approval_to" => $this->tenantUser->id,
            "details" => [
                [
                  "date" => date("Y-m-d H:i:s"),
                  "chart_of_account_id" => null,
                  "chart_of_account_name" => null,
                  "available" => $salesInvoice->amount,
                  "amount" => 800000,
                  "allocation_id" => null,
                  "allocation_name" => null,
                  "referenceable_form_date" => $salesInvoice->form->date,
                  "referenceable_form_number" => $salesInvoice->form->number,
                  "referenceable_form_notes" => $salesInvoice->form->notes,
                  "referenceable_id" => $salesInvoice->id,
                  "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesReturn->amount,
                    "amount" => 80000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesReturn->form->date,
                    "referenceable_form_number" => $salesReturn->form->number,
                    "referenceable_form_notes" => $salesReturn->form->notes,
                    "referenceable_id" => $salesReturn->id,
                    "referenceable_type" => "SalesReturn"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesDownPayment->amount,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesDownPayment->form->date,
                    "referenceable_form_number" => $salesDownPayment->form->number,
                    "referenceable_form_notes" => $salesDownPayment->form->notes,
                    "referenceable_id" => $salesDownPayment->id,
                    "referenceable_type" => "SalesDownPayment"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }

    public function getDummyWithoutCreate() {

        $salesInvoice  = SalesInvoice::orderBy('id', 'desc')->first();
        $salesReturn  = SalesReturn::orderBy('id', 'desc')->first();
        $salesDownPayment  = SalesDownPayment::orderBy('id', 'desc')->first();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => $this->customer->id,
            "customer_name" => $this->customer->name,
            "payment_type" => "cash",
            "request_approval_to" => $this->tenantUser->id,
            "details" => [
                [
                  "date" => date("Y-m-d H:i:s"),
                  "chart_of_account_id" => null,
                  "chart_of_account_name" => null,
                  "available" => $salesInvoice->amount,
                  "amount" => 400000,
                  "allocation_id" => null,
                  "allocation_name" => null,
                  "referenceable_form_date" => $salesInvoice->form->date,
                  "referenceable_form_number" => $salesInvoice->form->number,
                  "referenceable_form_notes" => $salesInvoice->form->notes,
                  "referenceable_id" => $salesInvoice->id,
                  "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesReturn->amount,
                    "amount" => 40000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesReturn->form->date,
                    "referenceable_form_number" => $salesReturn->form->number,
                    "referenceable_form_notes" => $salesReturn->form->notes,
                    "referenceable_id" => $salesReturn->id,
                    "referenceable_type" => "SalesReturn"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesDownPayment->amount,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesDownPayment->form->date,
                    "referenceable_form_number" => $salesDownPayment->form->number,
                    "referenceable_form_notes" => $salesDownPayment->form->notes,
                    "referenceable_id" => $salesDownPayment->id,
                    "referenceable_type" => "SalesDownPayment"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }

    public function getDummyInvalid() {
        if (!$this->customer) {
            $this->customer = factory(Customer::class)->create();
        }
        
        $this->createChartOfAccount();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => $this->customer->id,
            "customer_name" => $this->customer->name,
            "payment_type" => "cash",
            "request_approval_to" => $this->tenantUser->id,
            "details" => [
                [
                  "date" => date("Y-m-d H:i:s"),
                  "chart_of_account_id" => null,
                  "chart_of_account_name" => null,
                  "available" => 800000,
                  "amount" => 800000,
                  "allocation_id" => null,
                  "allocation_name" => null,
                  "referenceable_form_date" => '2022-08-01',
                  "referenceable_form_number" => 'SI22080001',
                  "referenceable_form_notes" => 'notes',
                  "referenceable_id" => '300',
                  "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }

    public function getDummyInvalidCustomer() {
        
        $this->createChartOfAccount();
        $this->createSalesInvoice();

        $salesInvoice  = SalesInvoice::orderBy('id', 'asc')->first();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => 900,
            "customer_name" => 'customer',
            "payment_type" => "cash",
            "request_approval_to" => $this->tenantUser->id,
            "details" => [
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesInvoice->amount,
                    "amount" => 800000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesInvoice->form->date,
                    "referenceable_form_number" => $salesInvoice->form->number,
                    "referenceable_form_notes" => $salesInvoice->form->notes,
                    "referenceable_id" => $salesInvoice->id,
                    "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }

    public function getDummyReferenceDone() {
        $this->createChartOfAccount();
        $this->createSalesInvoice();
        $this->createSalesReturn();
        $this->createDownPayment();

        $salesInvoice  = SalesInvoice::orderBy('id', 'desc')->first();
        $salesReturn  = SalesReturn::orderBy('id', 'desc')->first();
        $salesDownPayment  = SalesDownPayment::orderBy('id', 'desc')->first();

        $otherIncome = ChartOfAccount::where('position', 'CREDIT')->first();
        $otherExpense = ChartOfAccount::where('position', 'DEBIT')->first();
        
        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "customer_id" => $this->customer->id,
            "customer_name" => $this->customer->name,
            "payment_type" => "cash",
            "request_approval_to" => $this->tenantUser->id,
            "details" => [
                [
                  "date" => date("Y-m-d H:i:s"),
                  "chart_of_account_id" => null,
                  "chart_of_account_name" => null,
                  "available" => $salesInvoice->amount,
                  "amount" => $salesInvoice->amount,
                  "allocation_id" => null,
                  "allocation_name" => null,
                  "referenceable_form_date" => $salesInvoice->form->date,
                  "referenceable_form_number" => $salesInvoice->form->number,
                  "referenceable_form_notes" => $salesInvoice->form->notes,
                  "referenceable_id" => $salesInvoice->id,
                  "referenceable_type" => "SalesInvoice"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesReturn->amount,
                    "amount" => $salesReturn->amount,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesReturn->form->date,
                    "referenceable_form_number" => $salesReturn->form->number,
                    "referenceable_form_notes" => $salesReturn->form->notes,
                    "referenceable_id" => $salesReturn->id,
                    "referenceable_type" => "SalesReturn"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => null,
                    "chart_of_account_name" => null,
                    "available" => $salesDownPayment->amount,
                    "amount" => $salesDownPayment->amount,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => $salesDownPayment->form->date,
                    "referenceable_form_number" => $salesDownPayment->form->number,
                    "referenceable_form_notes" => $salesDownPayment->form->notes,
                    "referenceable_id" => $salesDownPayment->id,
                    "referenceable_type" => "SalesDownPayment"
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherIncome->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 200000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
                [
                    "date" => date("Y-m-d H:i:s"),
                    "chart_of_account_id" => $otherExpense->id,
                    "chart_of_account_name" => null,
                    "available" => 0,
                    "amount" => 100000,
                    "allocation_id" => null,
                    "allocation_name" => null,
                    "referenceable_form_date" => null,
                    "referenceable_form_number" => null,
                    "referenceable_form_notes" => null,
                    "referenceable_id" => null,
                    "referenceable_type" => null
                ],
            ]
        ];

        return $data;
    }
}