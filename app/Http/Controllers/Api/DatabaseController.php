<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Database\ViewTableRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    public function index(ViewTableRequest $request)
    {
        return response()->json([
            'data' => dbm_get_tables(get_tenant_db_name($request->get('project_code')), 'tenant')
        ]);
    }

    public function show(Request $request, $tableName)
    {
        $result = dbm_get_data(get_tenant_db_name($request->get('project_code')), $tableName, 'tenant');

        return $result;
    }
}
