<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Model\Project\Project;
use App\Model\Master\User;
use App\Model\Master\Item;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Mail\SalesVisitationNotificationMail;

class SalesVisitationNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:visitation:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Scheduled Sales Visitation Notification';

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
     * @return mixedf
     */
    public function handle()
    {
        $this->line('sending sales visitation notification');

        // Set timeformat to Indonesia and get yesterday's date
        setlocale(LC_TIME, 'id_ID');
        $yesterday_date = date('Y-m-d', strtotime("-1 days"));

        $projects = Project::all();

        foreach ($projects as $project) {
            $databaseName = 'point_'.strtolower($project->code);
            $this->line('Notification : ' . $project->code);

            // Update tenant database name in configuration
            config()->set('database.connections.tenant.database', strtolower($databaseName));
            DB::connection('tenant')->reconnect();

            $all_user = User::with('roles')->select(['id', 'email'])->get();
            $user_data = array();

            $queryCall = $this->queryCall($yesterday_date);
            $queryEffectiveCall = $this->queryEffectiveCall($yesterday_date);
            $queryValue = $this->queryValue($yesterday_date);
            $details = $this->queryDetails($yesterday_date);

            $result = User::leftJoinSub($queryCall, 'queryCall', function ($join) {
                $join->on('users.id', '=', 'queryCall.created_by');
            })->leftJoinSub($queryEffectiveCall, 'queryEffectiveCall', function ($join) {
                $join->on('users.id', '=', 'queryEffectiveCall.created_by');
            })->leftJoinSub($queryValue, 'queryValue', function ($join) {
                $join->on('users.id', '=', 'queryValue.created_by');
            })->select('users.id')
                ->addSelect('users.name as name')
                ->addSelect('users.first_name as first_name')
                ->addSelect('users.last_name as last_name')
                ->addSelect('users.email as email')
                ->addSelect('queryCall.total as call')
                ->addSelect('queryEffectiveCall.total as effective_call')
                ->addSelect('queryValue.value as value')
                ->groupBy('users.id')
                ->get();

            foreach ($result as $user) {
                $values = array_values($details->filter(function ($value) use ($user) {
                    $the_item = Item::select('name')->where('id', $value->item_id)->get()->first();
                    $value->item_name = $the_item->name;

                    return $value->created_by == $user->id;
                })->all());

                foreach ($values as $value) {
                    unset($value->created_by);
                }

                $user->items = $values;

                array_push($user_data, $user);
            }

            foreach ($all_user as $user) {
                foreach ($user_data as $data) {
                    $day_time = strftime("%A,%d-%m-%Y", strtotime($yesterday_date));
                    $sales_name = $data->first_name . ' ' . $data->last_name;
                    $call = ($data->call ?? 0);
                    $effective_call = ($data->effective_call ?? 0);
                    $items = $data->items;
                    $value = number_format(($data->value ?? 0), 3);

                    if (($user->hasPermissionTo("notification pin point sales") && $data->id == $user->id) || $user->hasPermissionTo("notification pin point supervisor")) {
                        Mail::to([$user->email])->queue(new SalesVisitationNotificationMail(
                            $project->name,
                            $day_time, 
                            $sales_name, 
                            $call, 
                            $effective_call, 
                            $items,
                            $value));
                    }
                }
            }
        }
    }

    public function queryCall($yesterday_date)
    {
        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select('forms.created_by as created_by')
            ->addselect(DB::raw('count(forms.id) as total'))
            ->where('date', $yesterday_date);
    }

    public function queryEffectiveCall($yesterday_date)
    {
        $querySalesVisitationHasDetail = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->where('forms.date', $yesterday_date)
            ->groupBy('pin_point_sales_visitations.id');

        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->addSelect('forms.created_by')
            ->addSelect(DB::raw('query_sales_visitation_has_detail.totalQty'))
            ->where('forms.date', $yesterday_date)
            ->groupBy('forms.created_by');
    }

    public function queryValue($yesterday_date)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->where('forms.date', $yesterday_date)
            ->addSelect('forms.created_by');
    }

    public function queryDetails($yesterday_date)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->rightJoin('items', 'items.id', '=', SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity) as quantity')
            ->addSelect('forms.created_by')
            ->addSelect('items.id as item_id')
            ->where('forms.date', $yesterday_date)
            ->orderBy('item_id')
            ->get();
    }
}