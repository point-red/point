<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\MemoJournal\StoreMemoJournalRequest;
use App\Http\Requests\Accounting\MemoJournal\UpdateMemoJournalRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Form;
use App\Model\Accounting\Journal;
use App\Exports\Accounting\MemoJournalExport;
use App\Model\Accounting\MemoJournal;
use App\Model\CloudStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Throwable;

class MemoJournalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $memoJournals = MemoJournal::from(MemoJournal::getTableName().' as '.MemoJournal::$alias)->eloquentFilter($request);
        
        $memoJournals = MemoJournal::joins($memoJournals, $request->get('join'));
        
        $memoJournals = pagination($memoJournals, $request->get('limit'));
        
        return new ApiCollection($memoJournals);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  -
     *  - items (Array) :
     *      - memo_journal_id (Int)
     *      - chart_of_account_id (Int)
     *      - chart_of_account_name (String)
     *      - masterable_id (Int)
     *      - masterable_type (String)
     *      - form_id (Int)
     *      - debit (Decimal)
     *      - credit (Decimal)
     *      - notes (String)
     *
     * @param StoreMemoJournalRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StoreMemoJournalRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $memoJournal = MemoJournal::create($request->all());
            $memoJournal
                ->load('form')
                ->load('items.chart_of_account');

            return new ApiResource($memoJournal);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $memoJournals = MemoJournal::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($memoJournals);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateMemoJournalRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateMemoJournalRequest $request, $id)
    {
        $memoJournal = MemoJournal::findOrFail($id);

        $result = DB::connection('tenant')->transaction(function () use ($request, $memoJournal) {
            $memoJournal->form->archive();
            $request['number'] = $memoJournal->form->edited_number;
            $request['old_increment'] = $memoJournal->form->increment;

            $memoJournal = MemoJournal::create($request->all());
            $memoJournal
                ->load('form')
                ->load('items.chart_of_account');

            return new ApiResource($memoJournal);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $memoJournal = MemoJournal::findOrFail($id);
        
        $memoJournal->requestCancel($request);

        DB::connection('tenant')->commit();

        return response()->json([], 204);
    }

    public function export(Request $request)
    {

        $request->validate([
            'data' => 'required',
        ]);
        
        $tenant = strtolower($request->header('Tenant'));

        $dateForm = date('d F Y', strtotime($request->data['date_start']));
        $dateTo = date('d F Y', strtotime($request->data['date_end']));
        
        $key = Str::random(16);
        $fileName = 'Memo Jurnal_'.$dateForm.'-'.$dateTo;
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

        Excel::store(new MemoJournalExport($request->data['date_start'], $request->data['date_end'], $request->data['ids'], $request->data['tenant_name']), $path, env('STORAGE_DISK'));

        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'memo journal';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->owner_id = auth()->user()->id;
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }

    public function formReferences(Request $request)
    {
        $journals = Journal::where('chart_of_account_id', $request->coa_id);
        if ($request->masterable_id && $request->masterable_type) {
            $journals = Journal::where([
                'chart_of_account_id' => $request->coa_id,
                'journalable_id' => $request->masterable_id,
                'journalable_type' => $request->masterable_type,
            ]);
        }

        $form_ids = $journals->pluck('form_id')->toArray();

        $forms = Form::whereIn('id', $form_ids)->select('id', 'number');

        if ($request->filter_like) {
            $forms->where('number', 'like', '%' . $request->filter_like . '%');
        };

        $forms = pagination($forms, $request->get('limit'));

        return new ApiCollection($forms);
    }

    public function dataFormReferences(Request $request)
    {
        if ($request->master_id) {
            $journal = Journal::where([
                'chart_of_account_id' => $request->coa_id,
                'form_id' => $request->form_id,
                'journalable_id' => $request->master_id
            ]);
        } else {
            $journal = Journal::where([
                'chart_of_account_id' => $request->coa_id,
                'form_id' => $request->form_id
            ]);
        }

        $journal =  $journal->first()
                            ->load('journalable');

        return new ApiResource($journal);
    }

}
