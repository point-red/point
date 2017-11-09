<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScaffoldingMasterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffolding:master {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate scaffolding feature master';

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
        $name = $this->argument('name');
        
        Artisan::call('make:model', [
            'name' => 'Model\\Master\\' . $name,
            '-m' => true,
        ]);

        $this->info('created ' . $name . ' model');

        Artisan::call('make:controller', [
            'name' => 'Api\\Master\\' . $name . 'Controller',
            '-r' => true,
        ]);

        $this->info('created ' . $name . ' controller');

        Artisan::call('make:resource', [
            'name' => 'Master\\' . $name . '\\' . $name . 'Resource',
        ]);

        $this->info('created ' . $name . ' resource');

        Artisan::call('make:resource', [
            'name' => 'Master\\' . $name . '\\' . $name . 'Collection',
            '--collection' => true,
        ]);

        $this->info('created ' . $name . ' collection');

        Artisan::call('make:request', [
            'name' => 'Master\\' . $name . '\\Store' . $name . 'Request'
        ]);

        $this->info('created ' . $name . ' store request');

        Artisan::call('make:request', [
            'name' => 'Master\\' . $name . '\\Update' . $name . 'Request'
        ]);

        $this->info('created ' . $name . ' update request');

        Artisan::call('make:factory', [
            'name' => $name . 'Factory',
        ]);

        $this->info('created ' . $name . ' factory');

        Artisan::call('make:test', [
            'name' => 'Master\\' . $name . 'RESTTest',
        ]);

        $this->info('created ' . $name . ' REST test class');

        Artisan::call('make:test', [
            'name' => 'Master\\' . $name . 'ValidationTest',
        ]);

        $this->info('created ' . $name . ' validation test class');
    }
}
