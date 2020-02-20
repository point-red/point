<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Accounting\ChartOfAccountType;
use Illuminate\Http\Request;

class ChartOfAccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $types = ChartOfAccountType::eloquentFilter($request);

        $types = pagination($types, $request->get('limit'));

        return new ApiCollection($types);
    }
}
