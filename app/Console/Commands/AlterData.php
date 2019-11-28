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
//            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $groups = Group::all();
            foreach ($groups as $group) {
                if ($group->class_reference == 'Customer' || $group->class_reference == Customer::class) {
                    $customerGroup = CustomerGroup::where('name', $group->name)->first();
                    if (!$customerGroup) {
                        $customerGroup = new CustomerGroup;
                        $customerGroup->name = $group->name;
                        $customerGroup->save();
                    }


                    $gs = DB::connection('tenant')->table('groupables')->where('group_id', $group->id)->get();
                    foreach ($gs as $g) {
                        $customerGroup->customers()->detach($g->groupable_id);
                        $customerGroup->customers()->attach($g->groupable_id, [
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }

                if ($group->class_reference == 'Supplier' || $group->class_reference == Supplier::class) {
                    $supplierGroup = SupplierGroup::where('name', $group->name)->first();
                    if (!$supplierGroup) {
                        $supplierGroup = new SupplierGroup;
                        $supplierGroup->name = $group->name;
                        $supplierGroup->save();
                    }

                    $gs = DB::connection('tenant')->table('groupables')->where('group_id', $group->id)->get();
                    foreach ($gs as $g) {
                        $supplierGroup->suppliers()->detach($g->groupable_id);
                        $supplierGroup->suppliers()->attach($g->groupable_id, [
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }

                if ($group->class_reference == 'Item' || $group->class_reference == Item::class) {
                    $itemGroup = ItemGroup::where('name', $group->name)->first();
                    if (!$itemGroup) {
                        $itemGroup = new ItemGroup;
                        $itemGroup->name = $group->name;
                        $itemGroup->save();
                    }

                    $gs = DB::connection('tenant')->table('groupables')->where('group_id', $group->id)->get();
                    foreach ($gs as $g) {
                        $itemGroup->items()->detach($g->groupable_id);
                        $itemGroup->items()->attach($g->groupable_id, [
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}
