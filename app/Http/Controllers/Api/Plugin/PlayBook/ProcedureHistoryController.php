<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook;

use App\Http\Controllers\Controller;
use App\Model\Plugin\PlayBook\Procedure;
use Illuminate\Http\Request;

class ProcedureHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Procedure $procedure)
    {
        $query = $procedure->histories()->latest();
        $histories = pagination($query, $request->limit ?: 10);

        return response()->json(compact('procedure', 'histories'));
    }
}
