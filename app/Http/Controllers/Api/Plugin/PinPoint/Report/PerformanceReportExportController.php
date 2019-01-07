<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint\Report;

use Carbon\Carbon;
use App\Model\CloudStorage;
use Illuminate\Http\Request;
use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PinPoint\Performance\PerformanceExport;

class PerformanceReportExportController extends Controller
{
    public function export(Request $request)
    {
        info('here'.$request->get('date_from'));
        $tenant = strtolower($request->header('Tenant'));
        $key = str_random(16);
        $fileName = strtoupper($tenant).' - Performance Report - '.date('M Y', strtotime($request->get('date_from')));
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
        $result = Excel::store(new PerformanceExport($request->get('date_from'), $request->get('date_to')), $path, env('STORAGE_DISK'));

        if (! $result) {
            return response()->json([
                'message' => 'Failed to export',
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
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }
}
