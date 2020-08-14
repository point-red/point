<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Plugin\PinPoint\SimilarProduct;
use Illuminate\Http\Request;

class SimilarProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $similarProducts = SimilarProduct::eloquentFilter($request);

        $similarProducts = pagination($similarProducts, $request->get('limit'));

        return new ApiCollection($similarProducts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ApiResource
     */
    public function store(Request $request)
    {
        $similarProduct = new SimilarProduct;
        $similarProduct->fill($request->all());
        $similarProduct->save();

        return new ApiResource($similarProduct);
    }
}
