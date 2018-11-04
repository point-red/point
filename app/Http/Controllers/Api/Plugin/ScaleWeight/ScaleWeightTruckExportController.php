<?php

namespace App\Http\Controllers\Api\Plugin\ScaleWeight;

use App\Exports\ScaleWeightTruckExport;
use App\Model\CloudStorage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ScaleWeightTruckExportController extends Controller
{
    public function export(Request $request)
    {
        $tenant = strtolower($request->header('Tenant'));
        $key = str_random(16);
        $fileName = strtoupper($tenant) .' - Scale Weight Truck Report - '. date('dMY', strtotime($request->get('date_from'))) . '-' . date('dMY', strtotime($request->get('date_to')));
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
        $result = Excel::store(new ScaleWeightTruckExport($request->get('date_from'), $request->get('date_to')), $path, env('STORAGE_DISK'));

        if (!$result) {
            return response()->json([
                'message' => 'Failed to export'
            ], 422);
        }

        $cloudStorage = new CloudStorage;
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'scale weight truck';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->tenant = $tenant;
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
