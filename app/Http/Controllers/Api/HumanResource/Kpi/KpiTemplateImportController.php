<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Factory;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\Kpi\KpiTemplateImport;
use App\Imports\Kpi\TemplateCheckImport;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Carbon\Carbon;

class KpiTemplateImportController extends Controller
{
    protected $filesystem;

    public function __construct(Factory $filesystem) {
        $this->filesystem   = $filesystem;
    }

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
            return response()->json([
              'message' => 'exist',
              'replace' => $exist->id,
              'name'    => $exist->name
            ], 200);
        }
    }

    public function import($file = null)
    {
      $data = request()->file('file');
        if (isset($data)) {
            $file = $data;

            if (isset(request()->replace)) {
              $kpiTemplate = KpiTemplate::where('id', request()->replace)->first();
              $kpiTemplate->delete();
            }
        }
        $import = new KpiTemplateImport();

        if (Excel::import($import, $file)) {
            return response()->json([
              'message' => 'success'
            ], 200);
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

        $save = $this->filesystem->disk(env('STORAGE_DISK'))->put($path, $file);

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
