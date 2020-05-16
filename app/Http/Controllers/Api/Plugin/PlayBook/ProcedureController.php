<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\PlayBook\Procedure\StoreProcedureRequest;
use App\Http\Resources\ApiCollection;
use App\Model\Plugin\PlayBook\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Procedure::query()
            ->parent()
            ->approved()
            ->filter($request)->orderBy('code');
        $procedures = pagination($query, $request->limit ?: 10);

        return new ApiCollection($procedures);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $query = Procedure::latest();

        if ($request->has('procedure_id')) {
            $query->whereProcedureId($request->procedure_id);
        } else {
            $query->parent();
        }

        $procedure = $query->first();

        if (! $procedure) {
            $procedure = Procedure::find($request->procedure_id);

            return response()->json([
                'code' => $procedure ? "{$procedure->code}.1" : null,
            ]);
        }

        $delimiter = '~*~';
        $onlyNumerics = explode(
            $delimiter,
            preg_replace('/[^0-9]/', $delimiter, $procedure->code),
        );
        $lastNumeric = $onlyNumerics[count($onlyNumerics) - 1];
        $nonIteration = substr(
            $procedure->code,
            0,
            strlen($procedure->code) - strlen("{$lastNumeric}")
        );

        return response()->json([
            'code' => $nonIteration.++$lastNumeric,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProcedureRequest $request)
    {
        $procedure = new Procedure($request->all());
        $procedure->approval_action = 'store';
        $procedure->save();

        $procedure->duplicateToHistory();

        return response()->json(compact('procedure'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Procedure $procedure)
    {
        return response()->json(compact('procedure'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Plugin\PlayBook\Procedure $procedure
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Procedure $procedure)
    {
        $request->validate([
            'code' => ['required', "unique:tenant.play_book_procedures,code,{$procedure->id}"],
            'name' => ['required'],
        ]);

        $approval = new Procedure($request->only('code', 'name', 'purpose', 'content', 'note'));
        $approval->approval_action = 'update';
        $approval->procedure_pending_id = $procedure->id;
        $approval->save();

        return response()->json(compact('procedure'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Procedure $procedure)
    {
        if (! $procedure->declined_at) {
            $approval = new Procedure([
                'code' => $procedure->code,
                'name' => $procedure->name,
                'purpose' => $procedure->purpose,
                'content' => $procedure->content,
                'note' => $procedure->note,
            ]);
            $approval->approval_action = 'destroy';
            $approval->procedure_pending_id = $procedure->id;
            $approval->save();
        } else {
            $procedure->delete();
        }

        return [
            'message' => 'Deleted',
        ];
    }
}
