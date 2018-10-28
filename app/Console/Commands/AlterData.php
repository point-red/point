<?php

namespace App\Console\Commands;

use App\Model\Project\Project;
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
            $this->line('Clone ' . $project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter ' . $project->code);
            config()->set('database.connections.tenant.database', 'point_' . strtolower($project->code));
            DB::connection('tenant')->reconnect();

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_types` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_types` ADD CONSTRAINT chart_of_account_types_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_types` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_types` ADD CONSTRAINT chart_of_account_types_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_groups` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_groups` ADD CONSTRAINT chart_of_account_groups_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_groups` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_account_groups` ADD CONSTRAINT chart_of_account_groups_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD CONSTRAINT chart_of_accounts_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `chart_of_accounts` ADD CONSTRAINT chart_of_accounts_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT employees_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employees` ADD CONSTRAINT employees_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_training_histories` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_training_histories` ADD CONSTRAINT employee_training_histories_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_training_histories` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_training_histories` ADD CONSTRAINT employee_training_histories_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_social_media` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_social_media` ADD CONSTRAINT employee_social_media_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_social_media` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_social_media` ADD CONSTRAINT employee_social_media_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_scorer` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_scorer` ADD CONSTRAINT employee_scorer_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_scorer` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_scorer` ADD CONSTRAINT employee_scorer_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_religions` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_religions` ADD CONSTRAINT employee_religions_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_religions` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_religions` ADD CONSTRAINT employee_religions_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_salary_histories` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_salary_histories` ADD CONSTRAINT employee_salary_histories_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_salary_histories` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_salary_histories` ADD CONSTRAINT employee_salary_histories_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_phones` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_phones` ADD CONSTRAINT employee_phones_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_phones` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_phones` ADD CONSTRAINT employee_phones_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_marital_statuses` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_marital_statuses` ADD CONSTRAINT employee_marital_statuses_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_marital_statuses` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_marital_statuses` ADD CONSTRAINT employee_marital_statuses_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_groups` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_groups` ADD CONSTRAINT employee_groups_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_groups` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_groups` ADD CONSTRAINT employee_groups_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_genders` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_genders` ADD CONSTRAINT employee_genders_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_genders` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_genders` ADD CONSTRAINT employee_genders_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_emails` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_emails` ADD CONSTRAINT employee_emails_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_emails` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_emails` ADD CONSTRAINT employee_emails_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` ADD CONSTRAINT employee_contracts_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_contracts` ADD CONSTRAINT employee_contracts_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_company_emails` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_company_emails` ADD CONSTRAINT employee_company_emails_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_company_emails` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_company_emails` ADD CONSTRAINT employee_company_emails_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_addresses` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_addresses` ADD CONSTRAINT employee_addresses_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `employee_addresses` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `employee_addresses` ADD CONSTRAINT employee_addresses_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` ADD CONSTRAINT cut_offs_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `cut_offs` ADD CONSTRAINT cut_offs_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_templates` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_templates` ADD CONSTRAINT kpi_templates_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_templates` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_templates` ADD CONSTRAINT kpi_templates_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_groups` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_groups` ADD CONSTRAINT kpi_template_groups_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_groups` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_groups` ADD CONSTRAINT kpi_template_groups_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` ADD CONSTRAINT kpi_template_indicators_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_indicators` ADD CONSTRAINT kpi_template_indicators_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_scores` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_scores` ADD CONSTRAINT kpi_template_scores_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_scores` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpi_template_scores` ADD CONSTRAINT kpi_template_scores_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpis` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpis` ADD CONSTRAINT kpis_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `kpis` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `kpis` ADD CONSTRAINT kpis_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD COLUMN created_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD CONSTRAINT warehouses_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD COLUMN updated_by int(10) unsigned');
            DB::connection('tenant')->statement('ALTER TABLE `warehouses` ADD CONSTRAINT warehouses_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES users(id)');

            DB::connection('tenant')->statement('DELETE FROM `migrations` WHERE migration = "2018_07_25_191457_create_journals_table"');
            DB::connection('tenant')->statement('DROP TABLE `journals`');

            $this->line('Migrate ' . $project->code);
            Artisan::call('tenant:migrate', ['db_name' => 'point_' . strtolower($project->code)]);
        }
    }
}
