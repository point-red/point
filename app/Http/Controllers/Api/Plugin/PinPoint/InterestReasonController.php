<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Customer\StoreCustomerRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Plugin\PinPoint\InterestReason;
use Illuminate\Http\Request;

class InterestReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $interestReasons = InterestReason::eloquentFilter($request);

        $interestReasons = pagination($interestReasons, $request->get('limit'));

        return new ApiCollection($interestReasons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $interestReason = new InterestReason;
        $interestReason->fill($request->all());
        $interestReason->save();

        return new ApiResource($interestReason);
    }
}
