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
    public function index(Request $request)
    {
        $cutOffs = CutOff::eloquentFilter($request);
        $cutOffs = CutOff::joins($cutOffs, $request);

        $cutOffs = pagination($cutOffs, $request->get('limit'));

        return new ApiCollection($cutOffs);
    }

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

        if (! $pdf) {
            return response()->json([
                'message' => 'Failed to export',
            ], 422);
        }

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'cut off';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->project_id = $project->id;
        $cloudStorage->owner_id = 1;
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
            CutOff::createCutoff($request->all());
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
    public function show(Request $request, $id)
    {
        $cutOff = CutOffAccount::eloquentFilter($request)->findOrFail($id);
        return new ApiResource($cutOff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $cutOff = CutOff::findOrFail($id);
        $cutOff->form->date = $request->get('date');
        $cutOff->form->save();

        return new ApiResource($cutOff);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Resources\Accounting\CutOff\CutOffResource
     */
    public function destroy($id)
    {
        DB::connection('tenant')->beginTransaction();

        $cutOff = CutOff::findOrFail($id);

        $cutOff->delete();

        Journal::where('journalable_type', CutOff::class)->where('journalable_id', $id)->delete();

        DB::connection('tenant')->commit();

        return new CutOffResource($cutOff);
    }
}
