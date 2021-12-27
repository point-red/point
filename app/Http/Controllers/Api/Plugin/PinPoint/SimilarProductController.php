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
        try {
            $similarProduct = new SimilarProduct;
            $similarProduct->name = str_clean($request->get("name"));
            $similarProduct->save();

            return new ApiResource($similarProduct);
        } catch (\Exception $err) {
            return response()->json(['message' => "Data exists!"], 400);
        }
    }
}
