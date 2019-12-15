<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateScaffoldingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffolding:generate {module} {name} {--submodule=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate scaffolding';

    protected $name;

    protected $module;

    protected $submodule;

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
        $this->module = $this->argument('module').DIRECTORY_SEPARATOR;
        $this->name = $this->argument('name');

        if ($this->option('submodule')) {
            $this->submodule = $this->option('submodule').DIRECTORY_SEPARATOR;
        }

        Artisan::call('make:model', [
            'name' => 'Model\\'.$this->module.$this->submodule.$this->name,
            '-m' => true,
        ]);

        $this->line('created '.$this->name.' model');

        Artisan::call('make:controller', [
            'name' => 'Api\\'.$this->module.$this->submodule.$this->name.'Controller',
            '-r' => true,
            '--api' => true,
        ]);

        $this->line('created '.$this->name.' controller');

        Artisan::call('make:resource', [
            'name' => $this->module.$this->submodule.$this->name.'\\'.$this->name.'Resource',
        ]);

        $this->line('created '.$this->name.' resource');

        Artisan::call('make:resource', [
            'name' => $this->module.$this->submodule.$this->name.'\\'.$this->name.'Collection',
            '--collection' => true,
        ]);

        $this->line('created '.$this->name.' collection');

        Artisan::call('make:request', [
            'name' => $this->module.$this->submodule.$this->name.'\\Store'.$this->name.'Request',
        ]);

        $this->line('created '.$this->name.' store request');

        Artisan::call('make:request', [
            'name' => $this->module.$this->submodule.$this->name.'\\Update'.$this->name.'Request',
        ]);

        $this->line('created '.$this->name.' update request');

        Artisan::call('make:factory', [
            'name' => $this->name.'Factory',
        ]);

        $this->line('created '.$this->name.' factory');

        Artisan::call('make:test', [
            'name' => $this->module.$this->submodule.$this->name.'Test',
        ]);

        $this->line('created '.$this->name.' test class');

        Artisan::call('make:test', [
            'name' => $this->module.$this->submodule.$this->name.'ValidationTest',
        ]);

        $this->line('created '.$this->name.' validation test class');
    }
}
