<?php

namespace App\Http\Controllers\Api\Project;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseController extends Controller
{
    public function index(Request $request)
    {
        Artisan::call('tenant:database:reset', [
            'project_code' => strtolower($request->get('Tenant'))
        ]);
    }
}
