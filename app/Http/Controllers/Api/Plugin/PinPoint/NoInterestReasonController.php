<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Plugin\PinPoint\NoInterestReason;
use Illuminate\Http\Request;

class NoInterestReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $noInterestReasons = NoInterestReason::eloquentFilter($request);

        $noInterestReasons = pagination($noInterestReasons, $request->get('limit'));

        return new ApiCollection($noInterestReasons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $noInterestReason = new NoInterestReason;
        $noInterestReason->fill($request->all());
        $noInterestReason->save();

        return new ApiResource($noInterestReason);
    }

}
