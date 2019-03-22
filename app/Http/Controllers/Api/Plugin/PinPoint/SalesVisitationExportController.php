<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Exports\PinPoint\SalesVisitationFormExport;
use App\Exports\PinPoint\ChartInterestReasonExport;
use App\Exports\PinPoint\ChartNotInterestReasonExport;
use App\Exports\PinPoint\ChartSimilarProductExport;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class SalesVisitationExportController extends Controller
{
    protected function exportFile($file, $dateFrom, $dateTo)
    {
      switch ($file) {
        case 'SalesVisitationReport':
          $export = new SalesVisitationFormExport($dateFrom, $dateTo);
          break;
        case 'ChartInterestReason':
          $export = new ChartInterestReasonExport($dateFrom, $dateTo);
          break;
        case 'ChartNotInterestReason':
          $export = new ChartNotInterestReasonExport($dateFrom, $dateTo);
          break;
        case 'ChartSimilarProduct':
          $export = new ChartSimilarProductExport($dateFrom, $dateTo);
          break;
      }

      return $export;
    }

    public function export(Request $request)
    {
        $fileExport = !empty($request->get('file_export')) ? $request->get('file_export') : 'SalesVisitationReport';

        info('here' . $request->get('date_from'));
        $tenant = strtolower($request->header('Tenant'));
        $key = str_random(16);
        $fileName = strtoupper($tenant)
            . ' - '.$fileExport.' - '
            . date('dMY', strtotime($request->get('date_from')))
            . '-'
            . date('dMY', strtotime($request->get('date_to')));
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
        $result = Excel::store($this->exportFile($fileExport, $request->get('date_from'), $request->get('date_to')), $path, env('STORAGE_DISK'));

        if (!$result) {
            return response()->json([
                'message' => 'Failed to export'
            ], 422);
        }

        $cloudStorage = new CloudStorage;
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'pin point sales visitation form';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
        $cloudStorage->owner_id = auth()->user()->id;
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key=' . $key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url
            ]
        ], 200);
    }
}
