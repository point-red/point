<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Exports\Kpi\KpiTemplateExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Model\CloudStorage;
use App\Model\Project\Project;

class KpiTemplateExportController extends Controller
{
    public function export(Request $request)
    {
      $request->validate([
        'id' => 'required|integer'
      ]);
      // return (new KpiTemplateExport(1))->download('KPI_export.xlsx');

      $tenant = strtolower($request->header('Tenant'));
      $key = str_random(16);
      $fileName = strtoupper($tenant)
          . ' - KPI Template Export - ';
      $fileExt = 'xlsx';
      $path = 'tmp/' . $tenant . '/' . $key . '.' . $fileExt;
      $result = Excel::store(new KpiTemplateExport($request->get('id')), $path, env('STORAGE_DISK'));

      if (!$result) {
          return response()->json([
              'message' => 'Failed to export'
          ], 422);
      }

      $cloudStorage = new CloudStorage();
      $cloudStorage->file_name = $fileName;
      $cloudStorage->file_ext = $fileExt;
      $cloudStorage->feature = 'kpi template';
      $cloudStorage->key = $key;
      $cloudStorage->path = $path;
      $cloudStorage->disk = env('STORAGE_DISK');
      $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
      $cloudStorage->owner_id = 1;
      $cloudStorage->expired_at = Carbon::now()->addDay(1);
      $cloudStorage->download_url = env('API_URL') . '/download?key=' . $key;
      $cloudStorage->save();

      return response()->json([
          'data' => [
              'url' => $cloudStorage->download_url
          ]
      ], 200);
    }
}
