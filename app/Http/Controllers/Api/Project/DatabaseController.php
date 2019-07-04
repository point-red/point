<?php

namespace App\Http\Controllers\Api\Project;

use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Database\ViewTableRequest;

class DatabaseController extends Controller
{
    public function index(ViewTableRequest $request, $id)
    {
        $project = Project::findOrFail($id);

        return response()->json([
            'data' => dbm_get_tables(get_tenant_db_name($project->code), 'tenant'),
        ]);
    }

    public function show($id, $tableName)
    {
        $project = Project::findOrFail($id);

        $result = dbm_get_data(get_tenant_db_name($project->code), $tableName, 'tenant');

        return $result;
    }
}
