<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report;

use App\Exports\PinPoint\Performance\PerformanceExport;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class PerformanceReportExportController extends Controller
{
    public function export(Request $request)
    {
        info('here' . $request->get('date_from'));
        $tenant = strtolower($request->header('Tenant'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $time = strtotime($dateFrom);
        $last = date('M Y', strtotime($dateTo));
        $files = [];

        do { //loop month in between date_from and date_to
          $month = date('M Y', $time);
          $days = date('t', $time);

          $key = str_random(16);
          $fileName = strtoupper($tenant) . ' - Performance Report - ' . $month;
          $fileExt = 'xlsx';
          $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
          $result = Excel::store(new PerformanceExport(date('Y-m-01', $time), date('Y-m-'.$days, $time)), $path, env('STORAGE_DISK'));

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

          $files[] = [
            'url' => $cloudStorage->download_url,
            'name' => $fileName
          ];

          $time = strtotime('+1 month', $time);
        } while ($month != $last);

        return response()->json([
            'data' => [
                'files' => $files
            ]
        ], 200);
    }
}
