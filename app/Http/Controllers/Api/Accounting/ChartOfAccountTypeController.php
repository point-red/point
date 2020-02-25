<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Accounting\ChartOfAccount;
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

        foreach ($types as $type) {
            $coa = ChartOfAccount::where('type_id', $type->id)->orderBy('number', 'desc')->first();
            $next_number = '';
            if ($coa && $coa->number) {
                $next_number = preg_replace('/[^0-9]/', '', $coa->number);
            }
            $type->next_number = (int) $next_number + 1;
        }

        return new ApiCollection($types);
    }
}
