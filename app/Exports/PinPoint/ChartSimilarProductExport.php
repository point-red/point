<?php

namespace App\Exports\PinPoint;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
class ChartSimilarProductExport implements FromView, WithCharts, WithTitle, ShouldAutoSize
{
    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = date('Y-m-d 00:00:00', strtotime($dateFrom));
        $this->dateTo = date('Y-m-d 23:59:59', strtotime($dateTo));
    }

    public function title(): string
    {
      return 'SimilarProduct';
    }

    public function view():view
    {
        $weeklySimilarProduct = [];

        $date = Carbon::parse(date('Y-m-01 00:00:00', strtotime($this->dateFrom)));
        $months = $date->daysInMonth;
        $j = 1;

        for ($i = 1; $i <= $months; $i++) {
            if ($date->englishDayOfWeek == 'Sunday') {
                $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($this->dateFrom));
                $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($this->dateTo));

                $weeklySimilarProduct[] = (object) [
                  'week' => $j . ' - ' . $i,
                  'products' => $this->query($dateFrom, $dateTo)
                ];

                $j = $i+1;
            }

            if ($i == $months && $date->englishDayOfWeek != 'Sunday') {
                $dateFrom = date('Y-m-'.$j.' 00:00:00', strtotime($this->dateFrom));
                $dateTo = date('Y-m-'.$i.' 23:59:59', strtotime($this->dateTo));

                $weeklySimilarProduct[] = (object) [
                  'week' => $j . ' - ' . $i,
                  'products' => $this->query($dateFrom, $dateTo)
                ];

                $j = $i+1;
            }

            $date->addDay(1);
        }

        $this->weeks = count($weeklySimilarProduct);

        return view('exports.plugin.pin-point.similarProduct', [
            'products' => $this->similarProducts()->pluck('name')->all(),
            'similarProducts' => $weeklySimilarProduct
        ]);
    }

    protected function cellColumns ()
    {
      $alpha = range('A', 'Z');

      $loop = $alpha;
      foreach ($loop as $keyF => $first) {
        foreach ($loop as $keyS => $second) {
          array_push($alpha, $first.$second);
        }
      }

      return $alpha;
    }

    /**
     * @return Chart|Chart[]
     */
    public function charts()
    {
      $cellColumns = $this->cellColumns();
      $columnPosition = 0;
      $lastColumn = $cellColumns[$this->similarProducts()->pluck('name')->count()];
      $firstData = 3;
      $charts = [];

      for ($i=0; $i < 5; $i++) {
        $cellRow = $firstData+$i;

        $label = [new DataSeriesValues('String', $this->title().'!$A$'.$cellRow, null, 1)]; //week
        $categories = [new DataSeriesValues('String', $this->title().'!$B$'.($firstData-1).':$'.$lastColumn.'$'.($firstData-1), null, 5)]; //products
        $values = [new DataSeriesValues('Number', $this->title().'!$B$'.$cellRow.':$'.$lastColumn.'$'.$cellRow, null, 5)]; //value

        $series = new DataSeries(DataSeries::TYPE_PIECHART, DataSeries::GROUPING_STANDARD,
        range(0, \count($values) - 1), $label, $categories, $values);

        $layout = new Layout();
        $layout->setShowVal(true);
        $layout->setShowPercent(true);

        $plot   = new PlotArea($layout, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $chart  = new Chart('Chart', new Title('Week'.($i+1)), $legend, $plot);

        $chart->setTopLeftPosition($cellColumns[$columnPosition].'9');
        $chart->setBottomRightPosition($cellColumns[$columnPosition+3].'19');

        $columnPosition = $columnPosition+4;
        $charts[] = $chart;
      }

      return $charts;
    }

    protected function similarProducts()
    {
      return SalesVisitationSimilarProduct::query()->distinct()
             ->select(SalesVisitationSimilarProduct::getTableName().'.name');
    }

    protected function query($dateFrom, $dateTo)
    {
      $query = SalesVisitationSimilarProduct::query()
          ->join(SalesVisitation::getTableName(),SalesVisitation::getTableName() . '.id', '=', SalesVisitationSimilarProduct::getTableName() . '.sales_visitation_id')
          ->join('forms', 'forms.id', '=', SalesVisitation::getTableName() . '.form_id')
          ->whereBetween('forms.date', [$this->dateFrom, $this->dateTo])
          ->where(SalesVisitationSimilarProduct::getTableName().'.name', '!=', '')
          ->selectRaw(SalesVisitationSimilarProduct::getTableName().'.name, count('.SalesVisitationSimilarProduct::getTableName().'.name) as total')
          ->groupBy(SalesVisitationSimilarProduct::getTableName().'.name');

      return $this->similarProducts()->leftJoinSub($query, 'query', function ($join)
              {
                  $join->on(SalesVisitationSimilarProduct::getTableName().'.name', '=', 'query.name');
              })
              ->addSelect('query.total')
              ->get();
    }

}
