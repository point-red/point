<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Exports\Kpi\KpiTemplateExport;
use App\Http\Controllers\Controller;
use App\Model\CloudStorage;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class KpiTemplateExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $kpiTemplate = KpiTemplate::where('id', $request->id)->first();

        $tenant = strtolower($request->header('Tenant'));
        $key = Str::random(16);
        $fileName = strtoupper($tenant)
          .' - KPI Template Export - '.$kpiTemplate->name;
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
        $result = Excel::store(new KpiTemplateExport($request->get('id')), $path, env('STORAGE_DISK'));

        if (! $result) {
            return response()->json([
                'message' => 'Failed to export',
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
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }
}
