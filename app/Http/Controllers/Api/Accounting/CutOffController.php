<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\CutOff\StoreRequest;
use App\Http\Resources\Accounting\CutOff\CutOffResource;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Accounting\CutOff;
use App\Model\Accounting\CutOffAccount;
use App\Model\Accounting\Journal;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use App\Model\Setting\SettingLogo;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CutOffController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return ApiCollection
     */
    public function indexByAccount(Request $request)
    {
        $cutOffs = CutOffAccount::eloquentFilter($request);
        $cutOffs = CutOffAccount::joins($cutOffs, $request);

        if($request->get("isDownload")) {
            return $this->exportByAccount($request, $cutOffs);
        }

        $cutOffs = pagination($cutOffs, $request->get('limit'));

        return new ApiCollection($cutOffs);
    }

    public function exportByAccount($request, $cutOffs)
    {
        $data = $cutOffs->get();

        $tenant = strtolower($request->header('Tenant'));
        $project = Project::where('code', $tenant)->first();
        $logo = SettingLogo::orderBy("id", 'desc')->first();
        $key = str_random(16);
        $fileName = strtoupper($tenant)
            .' - Cut Off Export';
        $fileExt = 'pdf';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        $pdfData = [
            'tenant' => $tenant,
            'data' => $data,
            'logo' => $logo,
            'address' => $project->address,
            'phone' => $project->phone,
        ];
        $pdf = PDF::loadView('exports.accounting.cutoff', $pdfData);
        $pdf = $pdf->setPaper('a4', 'portrait')->setWarnings(false);
        $pdf = $pdf->download()->getOriginalContent();
        Storage::disk(env('STORAGE_DISK'))->put($path, $pdf);

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'cut off';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = $project->id;
        $cloudStorage->owner_id = optional(auth()->user())->id;
        $cloudStorage->expired_at = Carbon::now()->addDay(1);
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => env('API_URL').'/download?key='.$key,
            ],
        ], 200);
    }

    public function totalCutoff(Request $request)
    {
        return CutOffAccount::selectRaw("SUM(debit) as debit, SUM(credit) as credit")->first();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResource
     */
    public function store(StoreRequest $request)
    {
        try {
            $cutoff = CutOff::createCutoff($request->all());
            return new ApiResource($cutoff);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function showByAccount(Request $request, $id)
    {
        $cutOff = CutOffAccount::eloquentFilter($request)->findOrFail($id);
        return new ApiResource($cutOff);
    }
}
