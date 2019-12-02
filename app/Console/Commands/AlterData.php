<?php

namespace App\Console\Commands;

use App\Model\Master\Customer;
use App\Model\Master\CustomerGroup;
use App\Model\Master\Group;
use App\Model\Master\Item;
use App\Model\Master\ItemGroup;
use App\Model\Master\Supplier;
use App\Model\Master\SupplierGroup;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AlterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
//            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            self::update('2014_10_12_000000_create_tenant_users_table');
            self::update('2014_10_12_100000_create_permission_tables');
            self::update('2019_01_01_001000_create_warehouses_table');
            self::update('2019_01_01_002000_create_kpi_templates_table');
            self::update('2019_01_01_002001_create_kpi_template_groups_table');
            self::update('2019_01_01_002002_create_kpi_template_indicators_table');
            self::update('2019_01_01_002003_create_kpi_template_scores_table');
            self::update('2019_01_01_002004_create_kpi_results_table');
            self::update('2019_01_01_002010_create_employee_groups_table');
            self::update('2019_01_01_002011_create_employee_religions_table');
            self::update('2019_01_01_002012_create_employee_marital_statuses_table');
            self::update('2019_01_01_002013_create_employee_genders_table');
            self::update('2019_01_01_002014_create_employee_statuses_table');
            self::update('2019_01_01_002015_create_employee_job_locations_table');
            self::update('2019_01_01_002016_create_employees_table');
            self::update('2019_01_01_002017_create_employee_contracts_table');
            self::update('2019_01_01_002018_create_employee_salary_histories_table');
            self::update('2019_01_01_002019_create_employee_training_histories_table');
            self::update('2019_01_01_002020_create_employee_promotion_histories_table');
            self::update('2019_01_01_002021_create_employee_emails_table');
            self::update('2019_01_01_002022_create_employee_social_media_table');
            self::update('2019_01_01_002030_create_kpis_table');
            self::update('2019_01_01_002031_create_kpi_groups_table');
            self::update('2019_01_01_002032_create_kpi_indicators_table');
            self::update('2019_01_01_003000_create_chart_of_account_types_table');
            self::update('2019_01_01_003001_create_chart_of_account_groups_table');
            self::update('2019_01_01_003002_create_chart_of_accounts_table');
            self::update('2019_01_01_003010_create_cut_offs_table');
            self::update('2019_01_01_003011_create_cut_off_details_table');
            self::update('2019_01_01_003020_create_scale_weight_trucks_table');
            self::update('2019_01_01_003021_create_scale_weight_items_table');
            self::update('2019_01_01_003022_create_employee_scorer_table');
            self::update('2019_01_01_003023_create_employee_addresses_table');
            self::update('2019_01_01_003024_create_employee_phones_table');
            self::update('2019_01_01_003025_create_employee_company_emails_table');
            self::update('2018_10_13_063954_create_pricing_groups_table');
            self::update('2018_10_13_063955_create_customers_table');
            self::update('2018_10_13_063958_create_expeditions_table');
            self::update('2018_10_13_064004_create_addresses_table');
            self::update('2018_10_13_064009_create_phones_table');
            self::update('2018_10_13_064442_create_emails_table');
            self::update('2018_10_13_064452_create_banks_table');
            self::update('2018_10_13_064500_create_contact_people_table');
            self::update('2018_10_13_154223_create_items_table');
            self::update('2018_10_13_164520_create_item_units_table');
            self::update('2018_10_13_164525_create_item_groups_table');
            self::update('2018_10_13_164526_create_item_item_group_table');
            self::update('2018_10_13_164528_create_customer_groups_table');
            self::update('2018_10_13_164529_create_supplier_groups_table');
            self::update('2018_10_13_164530_create_allocation_groups');
            self::update('2018_10_13_164531_create_service_groups');
            self::update('2018_10_17_051150_create_master_histories_table');
            self::update('2018_10_24_113039_create_pin_point_similar_products_table');
            self::update('2018_10_24_113224_create_pin_point_interest_reasons_table');
            self::update('2018_10_24_113233_create_pin_point_not_interest_reasons_table');
            self::update('2018_10_25_084904_create_suppliers_table');
            self::update('2018_10_26_040130_create_services_table');
            self::update('2018_10_26_040139_create_allocations_table');
            self::update('2018_10_26_061418_create_forms_table');
            self::update('2018_10_26_061431_create_form_approvals_table');
            self::update('2018_10_26_071518_create_form_cancellations_table');
            self::update('2018_10_26_072457_create_journals_table');
            self::update('2018_10_26_075732_create_inventories_table');
            self::update('2018_10_28_113809_create_pin_point_sales_visitations_table');
            self::update('2018_10_28_150726_create_pin_point_sales_visitation_details_table');
            self::update('2018_10_29_085250_create_pin_point_sales_visitation_interest_reasons_table');
            self::update('2018_10_29_085258_create_pin_point_sales_visitation_not_interest_reasons_table');
            self::update('2018_10_29_085318_create_pin_point_sales_visitation_similar_products_table');
            self::update('2018_11_08_080626_create_pin_point_sales_visitation_targets_table');
            self::update('2018_11_09_105820_create_price_list_items_table');
            self::update('2018_11_09_105828_create_price_list_services_table');
            self::update('2018_11_30_000001_create_purchase_requests_table');
            self::update('2018_11_30_000002_create_purchase_request_items_table');
            self::update('2018_11_30_000003_create_purchase_request_services_table');
            self::update('2018_11_30_000011_create_purchase_contracts_table');
            self::update('2018_11_30_000012_create_purchase_contract_items_table');
            self::update('2018_11_30_000013_create_purchase_contract_group_items_table');
            self::update('2018_11_30_000021_create_purchase_orders_table');
            self::update('2018_11_30_000022_create_purchase_order_items_table');
            self::update('2018_11_30_000023_create_purchase_order_services_table');
            self::update('2018_11_30_000031_create_purchase_receive_table');
            self::update('2018_11_30_000032_create_purchase_receive_items_table');
            self::update('2018_11_30_000033_create_purchase_receive_services_table');
            self::update('2018_11_30_000051_create_purchase_invoices_table');
            self::update('2018_11_30_000052_create_purchase_invoice_items_table');
            self::update('2018_11_30_000053_create_purchase_invoice_services_table');
            self::update('2018_11_30_000054_create_purchase_invoice_others_table');
            self::update('2018_11_30_000061_create_purchase_returns_table');
            self::update('2018_11_30_000062_create_purchase_return_items_table');
            self::update('2018_11_30_000063_create_purchase_return_services_table');
            self::update('2018_12_13_043000_create_sales_contracts_table');
            self::update('2018_12_13_043001_create_sales_contract_items_table');
            self::update('2018_12_13_043002_create_sales_contract_group_items_table');
            self::update('2018_12_13_043034_create_sales_quotations_table');
            self::update('2018_12_13_050709_create_sales_quotation_items_table');
            self::update('2018_12_13_050716_create_sales_quotation_services_table');
            self::update('2018_12_13_050923_create_sales_orders_table');
            self::update('2018_12_13_053135_create_sales_order_items_table');
            self::update('2018_12_13_053149_create_sales_order_services_table');
            self::update('2018_12_13_053854_create_delivery_orders_table');
            self::update('2018_12_13_095050_create_delivery_order_items_table');
            self::update('2018_12_13_095200_create_delivery_notes_table');
            self::update('2018_12_13_095219_create_delivery_note_items_table');
            self::update('2018_12_13_095256_create_sales_invoices_table');
            self::update('2018_12_13_095459_create_sales_invoice_items_table');
            self::update('2018_12_13_095504_create_sales_invoice_services_table');
            self::update('2018_12_13_100321_create_sales_invoice_others_table');
            self::update('2018_12_14_000001_create_sales_returns_table');
            self::update('2018_12_14_000002_create_sales_return_items_table');
            self::update('2018_12_14_000003_create_sales_return_services_table');
            self::update('2019_01_12_183312_create_kpi_scores_table');
            self::update('2019_01_21_002217_create_inventory_audits_table');
            self::update('2019_01_21_003515_create_inventory_audit_items_table');
            self::update('2019_01_26_084636_create_payments_table');
            self::update('2019_01_26_084648_create_payment_details_table');
            self::update('2019_01_26_084700_create_purchase_down_payments_table');
            self::update('2019_01_26_084700_create_purchase_payment_orders_table');
            self::update('2019_01_26_084701_create_purchase_payment_order_details_table');
            self::update('2019_01_26_084703_create_sales_payment_collections_table');
            self::update('2019_01_26_084704_create_sales_payment_collection_details_table');
            self::update('2019_01_26_084705_create_payment_orders_table');
            self::update('2019_01_26_084706_create_payment_order_details_table');
            self::update('2019_01_26_084750_create_purchase_down_payment_invoice');
            self::update('2019_01_26_084800_create_sales_down_payments_table');
            self::update('2019_01_26_084850_create_sales_down_payment_invoice');
            self::update('2019_01_30_001841_add_district_pin_point_sales_visitations');
            self::update('2019_03_17_120629_create_employee_salaries_table');
            self::update('2019_03_17_120655_create_employee_salary_assessments_table');
            self::update('2019_03_17_154530_create_employee_salary_assessment_scores_table');
            self::update('2019_03_17_154533_create_employee_salary_assessment_targets_table');
            self::update('2019_03_17_170653_create_employee_salary_achievements_table');
            self::update('2019_03_28_164907_create_opening_stocks_table');
            self::update('2019_01_01_000000_create_opening_stock_warehouses_table');
            self::update('2019_04_08_014937_create_setting_journals_table');
            self::update('2019_06_17_020620_create_allocation_reports_table');
            self::update('2019_06_26_115328_create_pos_bills_table');
            self::update('2019_06_26_115343_create_pos_bill_items_table');
            self::update('2019_08_02_090828_create_pos_bill_services_table');
            self::update('2019_08_08_114800_update_employee_salaries_table');
            self::update('2019_11_25_020333_customer_customer_group');
            self::update('2019_11_25_020439_supplier_supplier_group');
            self::update('2019_11_30_093149_create_service_service_group');
            self::update('2019_11_30_093155_create_allocation_allocation_group');
        }
    }

    public static function update($old, $new)
    {
        DB::connection('tenant')
            ->table('migrations')
            ->where('migration', 'like','%' . $old)
            ->update(['migration' => $new]);
    }
}
