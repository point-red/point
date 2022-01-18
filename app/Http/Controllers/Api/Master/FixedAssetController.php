<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\FixedAsset\DeleteFixedAssetRequest;
use App\Http\Requests\Master\FixedAsset\StoreFixedAssetRequest;
use App\Http\Requests\Master\FixedAsset\UpdateFixedAssetRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\FixedAsset;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $fixedAssets = FixedAsset::from(FixedAsset::getTableName().' as '.FixedAsset::$alias)->eloquentFilter($request);

        $fixedAssets = FixedAsset::joins($fixedAssets, $request->get('join'));

        if ($request->get('is_archived')) {
            $fixedAssets = $fixedAssets->whereNotNull('archived_at');
        } else {
            $fixedAssets = $fixedAssets->whereNull('archived_at');
        }

        $fixedAssets = pagination($fixedAssets, $request->get('limit'));

        return new ApiCollection($fixedAssets);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreFixedAssetRequest $request
     * @return ApiResource
     */
    public function store(StoreFixedAssetRequest $request)
    {
        $fixedAsset = new FixedAsset();
        $fixedAsset->fill($request->all());
        $fixedAsset->name = str_clean($request->get("name"));
        $fixedAsset->save();

        return new ApiResource($fixedAsset);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $fixedAsset = FixedAsset::from(FixedAsset::getTableName().' as '.FixedAsset::$alias)->eloquentFilter($request);

        $fixedAsset = FixedAsset::joins($fixedAsset, $request->get('join'));

        $fixedAsset = $fixedAsset->where(FixedAsset::$alias.'.id', $id)->first();

        return new ApiResource($fixedAsset);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateFixedAssetRequest $request
     * @param  int $id
     * @return ApiResource
     */
    public function update(UpdateFixedAssetRequest $request, $id)
    {
        $fixedAsset = FixedAsset::findOrFail($id);
        $fixedAsset->fill($request->only(['name', 'fixed_asset_group_id']));
        $fixedAsset->name = str_clean($request->get("name"));
        $fixedAsset->save();

        return new ApiResource($fixedAsset);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteFixedAssetRequest $request, $id)
    {
        FixedAsset::findOrFail($id)->delete();

        return response(null, 204);
    }

    /**
     * Display a listing of the fixed asset depreciation method.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function depreciationMethodList(Request $request)
    {
        $fixedAssetDepreciationMethods = FixedAsset::getAllDepreciationMethods();

        return new ApiResource($fixedAssetDepreciationMethods );
    }
}
