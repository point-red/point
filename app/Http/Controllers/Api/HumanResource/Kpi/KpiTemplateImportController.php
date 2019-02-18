<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Kpi\KpiTemplateImport;
use App\Imports\Kpi\TemplateCheckImport;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Storage;

class KpiTemplateImportController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
          'file' => 'required|mimes:xlsx,xls,csv|max:1024'
        ]);

        $file = $this->saveStorage($request->header('Tenant'), request()->file('file'));

        $KpiTemplate = (new TemplateCheckImport)->toArray($file);

        $exist = KpiTemplate::where('name', $KpiTemplate[0][1][1])->first();

        if ($exist == null) {
            $response = $this->import($file);
            return $response;
        } else {
            \Session::put('saveImport', $file);
            return response()->json('ada', 200);
        }
    }

    public function import($file = null)
    {
        if ($file == null) {
          $file = \Session::get('saveImport');
        }

        // dd($file. \Session::get('saveImport'));

        $import = new KpiTemplateImport();
        // $import->onlySheets(['Kpi Template', 'Kpi Template Group']);

        if (Excel::import($import, $file)) {
            return response()->json('ok', 200);
        }
    }

    public function saveStorage($tenantCode, $file)
    {
        $tenant = strtolower($tenantCode);
        $key = str_random(16);
        $fileName = strtoupper($tenant)
          . ' - KPI Template Import - ';
        $fileExt = 'xlsx';
        $path = 'tmp/' . $tenant . '/import';

        $save = Storage::disk(env('STORAGE_DISK'))->put($path, $file);

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'kpi template import';
        $cloudStorage->key = $key;
        $cloudStorage->path = $save;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
        $cloudStorage->owner_id = 1;
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL') . '/download?key=' . $key;
        $cloudStorage->save();

        return $save;
    }
}
