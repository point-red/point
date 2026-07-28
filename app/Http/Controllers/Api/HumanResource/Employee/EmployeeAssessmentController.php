<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiCollection;
use App\Http\Resources\HumanResource\Kpi\Kpi\KpiResource;
use App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource;
use App\Http\Resources\HumanResource\Kpi\KpiIndicator\KpiIndicatorResource;
use App\Mail\KpiReminderEmail;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Kpi\Automated;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiIndicator;
use App\Model\HumanResource\Kpi\KpiScore;
use App\Model\HumanResource\Employee\EmployeeScorer;
use App\Model\Notification;
use App\Model\FirebaseToken;
use App\Model\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\Firebase\Firestore;
use Carbon\Carbon;

// kpi reminder

class EmployeeAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $employeeId
     *
     * @return KpiCollection
     */
    public function index($employeeId)
    {
        $type = request()->get('type');

        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'));

        if ($type === 'all') {
            $kpis = $kpis->groupBy('kpis.id');
        } else {
            //? except all, it will count only completed status on the KPI.
            $kpis = $kpis->where('status', 'COMPLETED');
        }
        if ($type === 'daily') {
            $kpis = $kpis->groupBy('kpis.date');
        }
        if ($type === 'weekly') {
            $kpis = $kpis->groupBy(DB::raw('yearweek(kpis.date)'));
        }
        if ($type === 'monthly') {
            $kpis = $kpis->groupBy(DB::raw('year(kpis.date)'), DB::raw('month(kpis.date)'));
        }
        if ($type === 'yearly') {
            $kpis = $kpis->groupBy(DB::raw('year(kpis.date)'));
        }

        $kpis = $kpis->where('employee_id', $employeeId)->orderBy('kpis.date', 'desc')->orderBy('kpis.created_at', 'desc');

        $kpis = pagination($kpis, 15);

        $dates = [];
        $scores = [];

        foreach ($kpis as $key => $kpi) {
            array_push($dates, date('dMY', strtotime($kpi->date)));
            array_push($scores, number_format($kpi->score_percentage, 2));
        }

        return (new KpiCollection($kpis))
            ->additional([
                'data_set' => [
                    'dates' => $dates,
                    'scores' => $scores,
                ],
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param                           $employeeId
     * @return void
     * @throws \Exception
     */
    public function store(Request $request, $employeeId)
    {
        $template = $request->post('template');

        $date = $request->post('date');
        $dateFrom = isset($date['start']) ? $date['start'] : $date;
        $dateTo = isset($date['end']) ? $date['end'] : $date;
        $idIndicatorForAttach = 0;

        DB::connection('tenant')->beginTransaction();

        $kpi = new Kpi;
        $kpi->name = $template['name'];
        $kpi->date = date('Y-m-d', strtotime($dateTo));
        $kpi->employee_id = $employeeId;
        $kpi->scorer_id = auth()->user()->id;

        $comment = '';
        if (array_key_exists('comment', $template)) {
            $comment = $template['comment'];
        }
        $kpi->comment = $comment;

        $kpi->status = 'COMPLETED';
        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'])) {
                } else {
                    $kpi->status = 'DRAFT';
                }
            }
        }

        $kpi->save();

        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            $kpiGroup = new KpiGroup;
            $kpiGroup->kpi_id = $kpi->id;
            $kpiGroup->name = $template['groups'][$groupIndex]['name'];
            $kpiGroup->save();
            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                $kpiIndicator = new KpiIndicator;
                $kpiIndicator->kpi_group_id = $kpiGroup->id;
                $kpiIndicator->name = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['name'];
                $kpiIndicator->weight = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['weight'];
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['notes'])) {
                    $notes = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['notes'];
                    if (strlen($notes) > 4000) {
                        $notes = substr($notes, 0, 4001);
                    }
                    $kpiIndicator->notes = $notes;
                } else {
                    $kpiIndicator->notes = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['plan'])) {
                    $plan = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['plan'];
                    if (strlen($plan) > 4000) {
                        $plan = substr($plan, 0, 4001);
                    }
                    $kpiIndicator->plan = $plan;
                } else {
                    $kpiIndicator->plan = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['comment'])) {
                    $kpiIndicator->comment = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['comment'];
                } else {
                    $kpiIndicator->comment = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['attachment'])) {
                    $kpiIndicator->attachment = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['attachment'];
                } else {
                    $kpiIndicator->attachment = '';
                }
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['automated_code'])) {
                    $kpiIndicator->automated_code = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['automated_code'];

                    $data = Automated::getData($kpiIndicator->automated_code, $dateFrom, $dateTo, $employeeId);

                    $kpiIndicator->target = $data['target'];
                    $kpiIndicator->score = $data['score'];
                    $kpiIndicator->score_percentage = $kpiIndicator->target > 0 ? $kpiIndicator->score / $kpiIndicator->target * $kpiIndicator->weight : 0;

                    if ($kpiIndicator->score_percentage > $kpiIndicator->weight) {
                        $kpiIndicator->score_percentage = $kpiIndicator->weight;
                    }

                    $kpiIndicator->score_description = '';
                } else {
                    $kpiIndicator->target = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['target'];
                    if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'])) {
                        $kpiIndicator->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'];
                    } else {
                        $kpiIndicator->score = 0;
                    }

                    $kpiIndicator->score_percentage = $kpiIndicator->target > 0 ? $kpiIndicator->score / $kpiIndicator->target * $kpiIndicator->weight : 0;

                    if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['description'])) {
                        $kpiIndicator->score_description = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['description'];
                    } else {
                        $kpiIndicator->score_description = '';
                    }
                }

                $kpiIndicator->save();
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['attachment'])) {
                    $idIndicatorForAttach = $kpiIndicator->id;
                }

                if (!$template['groups'][$groupIndex]['indicators'][$indicatorIndex]['automated_code']) {
                    for ($scoreIndex = 0; $scoreIndex < count($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['scores']); $scoreIndex++) {
                        $kpiScore = new KpiScore();
                        $kpiScore->kpi_indicator_id = $kpiIndicator->id;
                        $kpiScore->description = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['scores'][$scoreIndex]['description'];
                        $kpiScore->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['scores'][$scoreIndex]['score'];
                        $kpiScore->status = $kpi->status;

                        $kpiScore->save();
                    }
                }
            }
        }
        $data = [
            'kpi_id' => $kpi->id,
            'indicator_id_attach' => $idIndicatorForAttach
        ];
        DB::connection('tenant')->commit();

        return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param $employeeId
     * @param int $id
     * @return KpiResource
     */
    public function show($employeeId, $id)
    {
        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'))
            ->where('employee_id', $employeeId)
            ->where('kpis.id', $id)
            ->first();

        $kpis->score = (float)$kpis->score;
        $kpis->target = (float)$kpis->target;

        return new KpiResource($kpis);
    }

    public function getByPeriode($employeeId, $date)
    {
        $employee = Employee::where('id', $employeeId)->first();
        $date = date('Y-m-d', strtotime($date . " -1 day"));
        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'))
            ->where('employee_id', $employeeId)
            ->where('scorer_id', $employee->user_id)
            ->whereDate('kpis.date', $date)
            ->first();

        $kpis->score = (float)$kpis->score;
        $kpis->target = (float)$kpis->target;

        return new KpiResource($kpis);
    }

    /**
     * Display the specified resource.
     *
     * @param $employeeId
     * @param $group
     * @return KpiResource
     */
    public function showBy(Request $request, $employeeId, $group)
    {
        return $this->buildAssessmentData($employeeId, $group, $request->get('type'));
    }

    /**
     * Build the per-template assessment table (per-assessor columns, TOTAL and
     * AVERAGE rows). Shared by showBy (JSON) and the monthly Excel export.
     */
    private function buildAssessmentData($employeeId, $group, $type)
    {
        $kpi = Kpi::find($group);
        $dateFilter = $kpi->date;
        $date = $kpi->getOriginal('date');
        $kpis = Kpi::join('kpi_groups', 'kpi_groups.kpi_id', '=', 'kpis.id')
            ->join('kpi_indicators', 'kpi_groups.id', '=', 'kpi_indicators.kpi_group_id')
            ->select('kpis.*')
            ->addSelect(DB::raw('sum(kpi_indicators.weight) / count(DISTINCT kpis.id) as weight'))
            ->addSelect(DB::raw('sum(kpi_indicators.target) / count(DISTINCT kpis.id) as target'))
            ->addSelect(DB::raw('sum(kpi_indicators.score) / count(DISTINCT kpis.id) as score'))
            ->addSelect(DB::raw('sum(kpi_indicators.score_percentage) / count(DISTINCT kpis.id) as score_percentage'))
            ->addSelect(DB::raw('count(DISTINCT kpis.id) as num_of_scorer'))
            ->where('employee_id', $employeeId)
            ->where('kpis.status', 'COMPLETED')
            ->groupBy('kpis.id');
        if ($type === 'daily') {
            $kpis = $kpis->where('kpis.date', $group);
        }
        if ($type === 'weekly') {
            $kpis = $kpis->where(DB::raw('yearweek(kpis.date)'), DB::raw("yearweek('$date')"));
        }
        if ($type === 'monthly') {
            $kpis = $kpis->where(DB::raw('EXTRACT(YEAR_MONTH from kpis.date)'), DB::raw("EXTRACT(YEAR_MONTH from '$date')"));
        }
        if ($type === 'yearly') {
            $kpis = $kpis->where(DB::raw('year(kpis.date)'), $group);
        }
        $kpis = $kpis->get();

        $result = [];
        $templates = [];
        $scorer = [];
        // splite based by template
        foreach ($kpis as $kpi) {
            if (!in_array($kpi->name, $templates)) {
                array_push($templates, $kpi->name);
            }
            $index = array_search($kpi->name, $templates);
            $result['data'][$index][] = $kpi;
        }
        // create data table
        $response = [];
        foreach ($templates as $index => $tmp) {
            $rows = [];
            $scorer = [];
            $employee = [];
            // combine every kpi score into one row per template
            foreach ($result['data'][$index] as $index2 => $kpi) {
                $kpi = new KpiResource($kpi);
                $employee = $kpi->employee;
                $scorer[] = $kpi->scorer;
                foreach ($kpi->groups as $index3 => $group) {
                    $group = new KpiGroupResource($group);
                    $sum = [
                        'weight' => 0,
                        'target' => 0,
                        'score' => 0,
                        'score_percentage' => 0,
                    ];
                    foreach ($group->indicators as $indicator) {
                        $indicator = new KpiIndicatorResource($indicator);
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['name'] = $indicator->name;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['weight'] = $indicator->weight;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['target'] = $indicator->target;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['notes'] = $indicator->notes;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['comment'] = $indicator->comment;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['data']['attachment'] = $indicator->attachment;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['scorer'][] = $indicator->score;
                        $rows[$index2][$group->name]['indicator'][$indicator->name]['scorer'][] = $indicator->score_percentage;
                        $sum['weight'] += $indicator->weight;
                        $sum['target'] += $indicator->target;
                        $sum['score'] += $indicator->score;
                        $sum['score_percentage'] += $indicator->score_percentage;
                    }
                    $rows[$index2][$group->name]['group']['weight'] = $sum['weight'];
                    $rows[$index2][$group->name]['group']['target'] = $sum['target'];
                    $rows[$index2][$group->name]['group']['scorer'][] = $sum['score'];
                    $rows[$index2][$group->name]['group']['scorer'][] = $sum['score_percentage'];
                }
            }
            // transpose
            $cols = [];
            $colsSum = [];
            foreach ($rows as $i => $row) {
                foreach ($row as $group => $col) {
                    $cols[$group]['data'][0] = $group;
                    $cols[$group]['data'][1] = $col['group']['weight'];
                    $cols[$group]['data'][2] = $col['group']['target'];
                    foreach ($col['group']['scorer'] as $val) {
                        $cols[$group]['data'][] = $val;
                    }
                    $cols[$group]['indicators'] = array_keys($col['indicator']);
                    foreach ($col['indicator'] as $key => $col) {
                        $cols[$group]['indicator'][$key][0] = $col['data']['name'];
                        $cols[$group]['indicator'][$key][1] = $col['data']['weight'];
                        $cols[$group]['indicator'][$key][2] = $col['data']['target'];
                        foreach ($col['scorer'] as $val) {
                            $cols[$group]['indicator'][$key][] = $val;
                        }
                    }
                }
            }
            // Insert an "Average" column pair (score, score_percentage) right
            // after Target on every row, computed as the mean across assessors.
            // A synthetic "Average" scorer is prepended so the UI/export render
            // it as the first assessor column (matching the agreed mockup).
            $numScorer = max(count($scorer), 1);
            $insertAverage = function (array $row) use ($numScorer) {
                $head = array_slice($row, 0, 3);   // label, weight, target
                $pairs = array_slice($row, 3);     // score/pct pairs per assessor
                $sumScore = 0;
                $sumPct = 0;
                $count = intdiv(count($pairs), 2);
                for ($i = 0; $i < $count; $i++) {
                    $sumScore += (float) ($pairs[$i * 2] ?? 0);
                    $sumPct += (float) ($pairs[$i * 2 + 1] ?? 0);
                }
                $avg = [round($sumScore / $numScorer, 2), round($sumPct / $numScorer, 2)];
                return array_merge($head, $avg, $pairs);
            };
            foreach ($cols as $group => $col) {
                $cols[$group]['data'] = $insertAverage($col['data']);
                foreach ($col['indicator'] as $key => $indRow) {
                    $cols[$group]['indicator'][$key] = $insertAverage($indRow);
                }
            }
            array_unshift($scorer, ['name' => 'Average', 'full_name' => 'Average']);

            $groupNames = array_keys($cols);
            $colsSum[0] = '';
            foreach ($groupNames as $i => $group) {
                foreach ($cols[$group]['indicators'] as $indicator) {
                    $it = 0;
                    foreach ($cols[$group]['indicator'][$indicator] as $val) {
                        if ($it == 0) {
                            $it++;
                            continue;
                        }
                        $col = (isset($colsSum[$it]) ? $colsSum[$it] : 0);
                        $colsSum[$it] = $col + $val;
                        $it++;
                    }
                }
            }
            $data = [
                'template' => $tmp,
                'date' => $dateFilter,
                'employee' => $employee,
                'scorer' => $scorer,
                'group' => array_keys($cols),
                'data' => $cols,
                'total' => $colsSum,
            ];
            $response[] = $data;
        }

        return $response;
    }

    /**
     * Export the monthly assessment table to Excel (one sheet per template).
     */
    public function exportBy(Request $request, $employeeId, $group)
    {
        $templates = $this->buildAssessmentData($employeeId, $group, $request->get('type'));

        $tenant = strtolower($request->header('Tenant'));
        $key = \Illuminate\Support\Str::random(16);
        $fileName = strtoupper($tenant).' - KPI Monthly Assessment';
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        $result = \Maatwebsite\Excel\Facades\Excel::store(
            new \App\Exports\Kpi\KpiMonthlyAssessmentExport($templates),
            $path,
            env('STORAGE_DISK')
        );

        if (! $result) {
            return response()->json(['message' => 'Failed to export'], 422);
        }

        $cloudStorage = new \App\Model\CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'kpi assessment';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', $tenant)->first()->id;
        // owner_id references users.id; use the requester, not a hardcoded 1
        // (which FK-fails on tenants where user #1 doesn't exist).
        $cloudStorage->owner_id = optional($request->user())->id;
        $cloudStorage->expired_at = \Carbon\Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json(['data' => ['url' => $cloudStorage->download_url]], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $employeeId
     * @param int $id
     * @return KpiResource
     */
    public function update(Request $request, $employeeId, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $isFeedback = false;
        $isComment = false;

        $kpi = Kpi::findOrFail($id);
        $kpi->comment = $request->get('comment');
      
        $template = $request->post('template');
      
        $kpi->status = 'COMPLETED';
        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'])) {
                } else {
                    $kpi->status = 'DRAFT';
                }
            }
        }

        $kpi->save();

        for ($groupIndex = 0; $groupIndex < count($template['groups']); $groupIndex++) {
            $kpiGroup = KpiGroup::findOrFail($template['groups'][$groupIndex]['id']);

            for ($indicatorIndex = 0; $indicatorIndex < count($template['groups'][$groupIndex]['indicators']); $indicatorIndex++) {
                $kpiIndicator = KpiIndicator::findOrFail($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['id']);
                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['notes'])) {
                    $notes = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['notes'];
                    if (strlen($notes) > 4000) {
                        $notes = substr($notes, 0, 4001);
                    }
                    $kpiIndicator->notes = $notes;
                    $isFeedback = true;
                } else {
                    $kpiIndicator->notes = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['plan'])) {
                    $plan = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['plan'];
                    if (strlen($plan) > 4000) {
                        $plan = substr($plan, 0, 4001);
                    }
                    $kpiIndicator->plan = $plan;
                    $isFeedback = true;
                } else {
                    $kpiIndicator->plan = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['comment'])) {
                    $kpiIndicator->comment = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['comment'];
                    $isComment = true;
                } else {
                    $kpiIndicator->comment = '';
                }

                if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['attachment'])) {
                    $kpiIndicator->attachment = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['attachment'];
                    $isUpdate = true;
                } else {
                    $kpiIndicator->attachment = '';
                }
                if (!$kpiIndicator->automated_code) {
                    $kpiIndicator->kpi_group_id = $kpiGroup->id;
                    $kpiIndicator->name = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['name'];
                    $kpiIndicator->weight = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['weight'];
                    $kpiIndicator->target = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['target'];

                    if (array_key_exists('selected', $kpiIndicator->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex])) {
                        if (get_if_set($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'])) {
                            $kpiIndicator->score = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['score'];
                            $kpiIndicator->score_percentage = $kpiIndicator->weight * $kpiIndicator->score / $kpiIndicator->target;
                            $kpiIndicator->score_description = $template['groups'][$groupIndex]['indicators'][$indicatorIndex]['selected']['description'];
                        } else {
                            $kpiIndicator->score = 0;
                            $kpiIndicator->score_percentage = 0;
                            $kpiIndicator->score_description = '';
                        }
                    }

                    $kpiIndicator->save();

                    for ($scoreIndex = 0; $scoreIndex < count($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['scores']); $scoreIndex++) {
                        $kpiScore = KpiScore::findOrFail($template['groups'][$groupIndex]['indicators'][$indicatorIndex]['scores'][$scoreIndex]['id']);
                        $kpiScore->status = $kpi->status;
                        $kpiScore->save();
                    }
                }
            }
        }

        DB::connection('tenant')->commit();

        return new KpiResource($kpi);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return KpiResource
     */
    public function destroy($employeeId, $id)
    {
        $kpi = Kpi::findOrFail($id);

        if ($kpi->scorer_id != auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 401);
        }

        $kpi->delete();

        return new KpiResource($kpi);
    }

    // kpi reminder
    public function kpiReminder(Request $request)
    {
        Mail::to($request->get('to'))->send(new KpiReminderEmail());
        return response()->json(['message' => 'message sent to ' . $request->get('to')], 200);
    }

    // create function to send notification to user from kpi assessment
    /**
     * Send notification to user for KPI assessment.
     * This function is intended to be called via API, based on existing assessment data.
     *
     * @param int $userId
     * @param \App\Model\Project\Project $project
     * @param string $clickAction
     * @param string $userTokens
     * @param string $title
     * @param string $message
     * @param int|null $kpiId
     * @param int|null $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Send notification to user for KPI assessment based on assessmentId.
     *
     * @param int $assessmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotificationKpiAssessment(Request $request, $id)
    {
        // Get KPI assessment
        $kpi = Kpi::findOrFail($id);
        $employeeId = $kpi->employee_id;

        if ($kpi->status != 'COMPLETED') {
            return response()->json([
                'success' => false,
                'message' => 'KPI assessment is not completed. Notification cannot be sent.',
            ], 200);
        }

        $scorer = EmployeeScorer::where('employee_id', $employeeId)->pluck('user_id')->toArray();
        $userIdCurrent = Employee::where('id', $employeeId)->first();

        if ($userIdCurrent->user_id == auth()->user()->id) {
            // $userId = $scorer->user_id;
            $userId = $scorer;

            $isNew = $request->get('isNew');

            if ($isNew) {
                $message = auth()->user()->first_name.' '.auth()->user()->last_name. ' has submitted a new KPI';
                $title = "New KPI Submission Alert!";
            } else {
                $message = 'There\'s new update from '. auth()->user()->first_name.' '.auth()->user()->last_name;
                $title = "Update on Your KPI Report";
            }

            
        } else {
            $userId = [$userIdCurrent->user_id];

            $message = auth()->user()->first_name.' '.auth()->user()->last_name. ' has left feedback';
            $title = "KPI Feedback Received";
        }            

        $tenant = strtolower($request->header('Tenant'));
        $project = Project::where('code', $tenant)->first();  

        $userTokens = FirebaseToken::whereIn('user_id', $userId)
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $domainProject = (env('APP_ENV') === 'local' ? 'http://' : 'https://').$project->code.'.'.env('TENANT_DOMAIN');

        $clickAction = '/human-resource/kpi/kpi-assessment/'.$employeeId.'/assessment/'.$id; //(env('APP_ENV') === 'local' ? 'http://' : 'https://').$project->code.'.'.env('TENANT_DOMAIN').          
        
        foreach ($userTokens as $userToken) {
            // dd($userToken->token)
            sendNotification($userToken->user_id, $project, $clickAction, $userToken->token, $title, $message, $domainProject);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully.',
        ]);
    }
}
