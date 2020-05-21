<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AlterMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary function to reorder database migration before v1.0';

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
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->table('migrations')->truncate();

            // USER & ROLE - PERMISSION
            self::update('2014_10_12_000000_create_tenant_users_table');
            self::update('2014_10_12_010000_create_branches_table');
            self::update('2014_10_12_020000_create_branch_user_table');
            self::update('2014_10_12_100000_create_permission_tables');
            // KPI TEMPLATES
            self::update('2019_01_01_100000_create_kpi_templates_table');
            self::update('2019_01_01_100001_create_kpi_template_groups_table');
            self::update('2019_01_01_100002_create_kpi_template_indicators_table');
            self::update('2019_01_01_100003_create_kpi_template_scores_table');
            self::update('2019_01_01_100004_create_kpi_results_table');
            // Employee
            self::update('2019_01_01_100010_create_employee_groups_table');
            self::update('2019_01_01_100011_create_employee_religions_table');
            self::update('2019_01_01_100012_create_employee_marital_statuses_table');
            self::update('2019_01_01_100013_create_employee_genders_table');
            self::update('2019_01_01_100014_create_employee_statuses_table');
            self::update('2019_01_01_100015_create_employee_job_locations_table');
            self::update('2019_01_01_100016_create_employees_table');
            self::update('2019_01_01_100017_create_employee_contracts_table');
            self::update('2019_01_01_100018_create_employee_salary_histories_table');
            self::update('2019_01_01_100019_create_employee_training_histories_table');
            self::update('2019_01_01_100020_create_employee_promotion_histories_table');
            self::update('2019_01_01_100021_create_employee_emails_table');
            self::update('2019_01_01_100022_create_employee_social_media_table');
            self::update('2019_01_01_100023_create_employee_scorer_table');
            self::update('2019_01_01_100024_create_employee_addresses_table');
            self::update('2019_01_01_100025_create_employee_phones_table');
            self::update('2019_01_01_100026_create_employee_company_emails_table');
            // EMPLOYEE KPI SCORE
            self::update('2019_01_01_100100_create_kpis_table');
            self::update('2019_01_01_100101_create_kpi_groups_table');
            self::update('2019_01_01_100102_create_kpi_indicators_table');
            self::update('2019_01_01_100103_create_kpi_scores_table');
            // Employee Salary
            self::update('2019_01_01_100110_create_employee_salaries_table');
            self::update('2019_01_01_100111_create_employee_salary_assessments_table');
            self::update('2019_01_01_100112_create_employee_salary_assessment_scores_table');
            self::update('2019_01_01_100113_create_employee_salary_assessment_targets_table');
            self::update('2019_01_01_100114_create_employee_salary_achievements_table');
            // Chart of Account
            self::update('2019_01_01_110000_create_chart_of_account_types_table');
            self::update('2019_01_01_110001_create_chart_of_account_groups_table');
            self::update('2019_01_01_111000_create_chart_of_accounts_table');
            // ======================================================================
            // MASTER
            // ======================================================================
            self::update('2019_01_01_120000_create_warehouses_table');
            // --
            self::update('2019_01_01_120001_create_items_table');
            self::update('2019_01_01_120002_create_item_units_table');
            self::update('2019_01_01_120003_create_item_groups_table');
            self::update('2019_01_01_120004_create_item_item_group_table');
            // --
            self::update('2019_01_01_120005_create_services_table');
            self::update('2019_01_01_120006_create_service_groups');
            self::update('2019_01_01_120007_create_service_service_group');
            // --
            self::update('2019_01_01_120008_create_pricing_groups_table');
            self::update('2019_01_01_120009_create_price_list_items_table');
            self::update('2019_01_01_120010_create_price_list_services_table');
            // --
            self::update('2019_01_01_120020_create_customers_table');
            self::update('2019_01_01_120021_create_customer_groups_table');
            self::update('2019_01_01_120022_create_customer_customer_group_table');
            // --
            self::update('2019_01_01_120030_create_suppliers_table');
            self::update('2019_01_01_120031_create_supplier_groups_table');
            self::update('2019_01_01_120032_create_supplier_supplier_group_table');
            // --
            self::update('2019_01_01_120040_create_expeditions_table');
            // --
            self::update('2019_01_01_120100_create_allocations_table');
            self::update('2019_01_01_120101_create_allocation_groups_table');
            self::update('2019_01_01_120102_create_allocation_allocation_group_table');

            // Person Relation
            self::update('2019_01_01_121000_create_addresses_table');
            self::update('2019_01_01_121001_create_phones_table');
            self::update('2019_01_01_121002_create_emails_table');
            self::update('2019_01_01_121003_create_banks_table');
            self::update('2019_01_01_121004_create_contact_people_table');
            // --
            self::update('2019_01_01_199000_create_master_histories_table');

            // ===============================================================
            // Transaction
            // ===============================================================
            self::update('2019_01_01_200000_create_forms_table');
            // Mutation Table
            self::update('2019_01_01_200010_create_journals_table');
            self::update('2019_01_01_200011_create_inventories_table');
            self::update('2019_01_01_200012_create_allocation_reports_table');
            // Accounting
            self::update('2019_01_01_210000_create_cut_offs_table');
            self::update('2019_01_01_211000_create_cut_off_accounts_table');
            self::update('2019_01_01_212000_create_cut_off_inventories_table');
            self::update('2019_01_01_213000_create_cut_off_account_payables_table');
            self::update('2019_01_01_214000_create_cut_off_account_receivables_table');
            self::update('2019_01_01_215000_create_cut_off_purchase_down_payments_table');
            self::update('2019_01_01_216000_create_cut_off_sales_down_payments_table');
            self::update('2019_01_01_219999_create_setting_journals_table');
            // Inventory
            self::update('2019_01_01_220000_create_opening_stocks_table');
            self::update('2019_01_01_220001_create_opening_stock_warehouses_table');
            self::update('2019_01_01_220002_create_inventory_audits_table');
            self::update('2019_01_01_220003_create_inventory_audit_items_table');
            // Purchasing
            self::update('2019_01_01_230000_create_purchase_requests_table');
            self::update('2019_01_01_230001_create_purchase_request_items_table');
            self::update('2019_01_01_230002_create_purchase_request_services_table');
            self::update('2019_01_01_230003_create_purchase_contracts_table');
            self::update('2019_01_01_230004_create_purchase_contract_items_table');
            self::update('2019_01_01_230005_create_purchase_contract_group_items_table');
            self::update('2019_01_01_230006_create_purchase_orders_table');
            self::update('2019_01_01_230007_create_purchase_order_items_table');
            self::update('2019_01_01_230008_create_purchase_order_services_table');
            self::update('2019_01_01_230009_create_purchase_receive_table');
            self::update('2019_01_01_230010_create_purchase_receive_items_table');
            self::update('2019_01_01_230011_create_purchase_receive_services_table');
            self::update('2019_01_01_230012_create_purchase_invoices_table');
            self::update('2019_01_01_230013_create_purchase_invoice_items_table');
            self::update('2019_01_01_230014_create_purchase_invoice_services_table');
            self::update('2019_01_01_230015_create_purchase_invoice_others_table');
            self::update('2019_01_01_230016_create_purchase_returns_table');
            self::update('2019_01_01_230017_create_purchase_return_items_table');
            self::update('2019_01_01_230018_create_purchase_return_services_table');
            // Sales
            self::update('2019_01_01_231000_create_sales_contracts_table');
            self::update('2019_01_01_231001_create_sales_contract_items_table');
            self::update('2019_01_01_231002_create_sales_contract_group_items_table');
            self::update('2019_01_01_231003_create_sales_quotations_table');
            self::update('2019_01_01_231004_create_sales_quotation_items_table');
            self::update('2019_01_01_231005_create_sales_quotation_services_table');
            self::update('2019_01_01_231006_create_sales_orders_table');
            self::update('2019_01_01_231007_create_sales_order_items_table');
            self::update('2019_01_01_231008_create_sales_order_services_table');
            self::update('2019_01_01_231009_create_delivery_orders_table');
            self::update('2019_01_01_231010_create_delivery_order_items_table');
            self::update('2019_01_01_231011_create_delivery_notes_table');
            self::update('2019_01_01_231012_create_delivery_note_items_table');
            self::update('2019_01_01_231013_create_sales_invoices_table');
            self::update('2019_01_01_231014_create_sales_invoice_items_table');
            self::update('2019_01_01_231015_create_sales_invoice_services_table');
            self::update('2019_01_01_231016_create_sales_invoice_others_table');
            self::update('2019_01_01_231017_create_sales_returns_table');
            self::update('2019_01_01_231018_create_sales_return_items_table');
            self::update('2019_01_01_231019_create_sales_return_services_table');
            // Point of Sales
            self::update('2019_01_01_232000_create_pos_bills_table');
            self::update('2019_01_01_232001_create_pos_bill_items_table');
            self::update('2019_01_01_232002_create_pos_bill_services_table');
            // Finance - Payment
            self::update('2019_01_01_233000_create_payments_table');
            self::update('2019_01_01_233001_create_payment_details_table');
            // --
            self::update('2019_01_01_233010_create_purchase_down_payments_table');
            self::update('2019_01_01_233011_create_purchase_down_payment_invoice_table');
            self::update('2019_01_01_233012_create_purchase_payment_orders_table');
            self::update('2019_01_01_233013_create_purchase_payment_order_details_table');
            // --
            self::update('2019_01_01_233020_create_sales_payment_collections_table');
            self::update('2019_01_01_233021_create_sales_payment_collection_details_table');
            self::update('2019_01_01_233022_create_sales_down_payments_table');
            self::update('2019_01_01_233023_create_sales_down_payment_invoice_table');
            // --
            self::update('2019_01_01_233030_create_payment_orders_table');
            self::update('2019_01_01_233031_create_payment_order_details_table');
            // Plugin - Pin Point
            self::update('2019_01_01_900100_create_pin_point_similar_products_table');
            self::update('2019_01_01_900101_create_pin_point_interest_reasons_table');
            self::update('2019_01_01_900102_create_pin_point_no_interest_reasons_table');
            self::update('2019_01_01_900103_create_pin_point_sales_visitations_table');
            self::update('2019_01_01_900104_create_pin_point_sales_visitation_details_table');
            self::update('2019_01_01_900105_create_pin_point_sales_visitation_interest_reasons_table');
            self::update('2019_01_01_900106_create_pin_point_sales_visitation_no_interest_reasons_table');
            self::update('2019_01_01_900107_create_pin_point_sales_visitation_similar_products_table');
            self::update('2019_01_01_900108_create_pin_point_sales_visitation_targets_table');
            // Plugins - Scale Weight
            self::update('2019_01_01_900200_create_scale_weight_trucks_table');
            self::update('2019_01_01_900201_create_scale_weight_items_table');
            // Manufacture
            self::update('2019_12_04_083102_create_manufacture_machines_table');
            self::update('2019_12_04_083359_create_manufacture_processes_table');
            self::update('2019_12_05_083638_create_manufacture_formulas_table');
            self::update('2019_12_05_085229_create_manufacture_formula_raw_materials_table');
            self::update('2019_12_05_095229_create_manufacture_formula_finished_goods_table');
            self::update('2019_12_09_065809_create_manufacture_inputs_table');
            self::update('2019_12_09_070805_create_manufacture_input_raw_materials_table');
            self::update('2019_12_09_070905_create_manufacture_input_finished_goods_table');
            self::update('2019_12_09_075809_create_manufacture_outputs_table');
            self::update('2019_12_09_080905_create_manufacture_output_finished_goods_table');

            // UserWarehouse
            self::update('2019_12_16_034758_create_user_warehouse_table');
        }
    }

    public static function update($migration)
    {
        DB::connection('tenant')
            ->table('migrations')
            ->insert(['migration' => $migration, 'batch' => 1]);
    }
}
