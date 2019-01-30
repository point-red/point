<?php

namespace App\Exports\PinPoint\Performance;

use App\Model\Master\Item;
use App\Model\Master\User;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class WeeklySheet implements FromView, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    /**
     * Constructor.
     *
     * @param string $date
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $totalDay
     */
    public function __construct(string $date, string $dateFrom, string $dateTo, int $totalDay)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
        $this->date = $date;
        $this->totalDay = $totalDay;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->date;
    }

    public function queryTarget($dateTo)
    {
        $query = SalesVisitationTarget::whereIn('date', function ($query) use ($dateTo) {
            $query->selectRaw('max(date)')->from(SalesVisitationTarget::getTableName())->where('date', '<=', $dateTo)->groupBy('user_id');
        });

        $targets = User::leftJoinSub($query, 'query', function ($join) {
            $join->on('users.id', '=', 'query.user_id');
        })->select('query.id as id')
            ->addSelect('users.name as name')
            ->addSelect('users.id as user_id')
            ->addSelect('query.date as date')
            ->addSelect('query.call as call')
            ->addSelect('query.effective_call as effective_call')
            ->addSelect('query.value as value')
            ->groupBy('users.id');

        return $targets;
    }

    public function queryCall($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select('forms.created_by as created_by')
            ->addselect(DB::raw('count(forms.id) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public function queryEffectiveCall($dateFrom, $dateTo)
    {
        $querySalesVisitationHasDetail = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('pin_point_sales_visitations.id');

        return SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->addSelect('forms.created_by')
            ->addSelect(DB::raw('query_sales_visitation_has_detail.totalQty'))
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->groupBy('forms.created_by');
    }

    public function queryValue($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->addSelect('forms.created_by');
    }

    public function queryDetails($dateFrom, $dateTo)
    {
        return SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->leftJoin(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->rightJoin('items', 'items.id', '=', SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy(SalesVisitationDetail::getTableName().'.item_id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity) as quantity')
            ->addSelect('forms.created_by')
            ->addSelect('items.id as item_id')
            ->addSelect('items.name as name')
            ->whereBetween('forms.date', [$dateFrom, $dateTo])
            ->orderBy('item_id')
            ->get();
    }

    /**
     * @return View
     */
    public function view(): View
    {
        $queryTarget = $this->queryTarget($this->dateTo);
        $queryCall = $this->queryCall($this->dateFrom, $this->dateTo);
        $queryEffectiveCall = $this->queryEffectiveCall($this->dateFrom, $this->dateTo);
        $queryValue = $this->queryValue($this->dateFrom, $this->dateTo);
        $details = $this->queryDetails($this->dateFrom, $this->dateTo);

        $users = User::query()->leftJoinSub($queryTarget, 'queryTarget', function ($join) {
            $join->on('users.id', '=', 'queryTarget.user_id');
        })->leftJoinSub($queryCall, 'queryCall', function ($join) {
            $join->on('users.id', '=', 'queryCall.created_by');
        })->leftJoinSub($queryEffectiveCall, 'queryEffectiveCall', function ($join) {
            $join->on('users.id', '=', 'queryEffectiveCall.created_by');
        })->leftJoinSub($queryValue, 'queryValue', function ($join) {
            $join->on('users.id', '=', 'queryValue.created_by');
        })->select('users.id')
            ->addSelect('users.name')
            ->addSelect('users.first_name')
            ->addSelect('users.last_name')
            ->addSelect('queryTarget.call as target_call')
            ->addSelect('queryTarget.effective_call as target_effective_call')
            ->addSelect('queryTarget.value as target_value')
            ->addSelect('queryCall.total as actual_call')
            ->addSelect('queryEffectiveCall.total as actual_effective_call')
            ->addSelect('queryValue.value as actual_value')
            ->where('queryTarget.call', '>', 0)
            ->groupBy('users.id')
            ->get();

        foreach ($users as $user) {
            $values = array_values($details->filter(function ($value) use ($user) {
                return $value->created_by == $user->id;
            })->all());

            foreach ($values as $value) {
                unset($value->created_by);
            }

            $user->items = $values;
        }

        return view('exports.plugin.pin-point.performance.weekly', [
            'users' => $users,
            'items' => Item::all(),
            'totalDay' => $this->totalDay,
            'targetCall' => 0,
            'targetEffectiveCall' => 0,
            'targetValue' => 0,
            'actualCall' => 0,
            'actualEffectiveCall' => 0,
            'actualValue' => 0,
            'actualCallPercentage' => 0,
            'actualEffectiveCallPercentage' => 0,
            'actualValuePercentage' => 0,
            'totalItemSold' => [],
        ]);
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_PERCENTAGE_00,
            'J' => NumberFormat::FORMAT_PERCENTAGE_00,
            'K' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:K2')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:K2')->getFont()->setSize(13);
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '00000000']
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];
                $event->getSheet()->getStyle('A1:K2')->applyFromArray($styleArray);
            },
        ];
    }
}
