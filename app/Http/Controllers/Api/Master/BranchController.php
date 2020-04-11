<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Branch\StoreRequest;
use App\Http\Requests\Master\Branch\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $branches = Branch::from(Branch::getTableName() . ' as ' . Branch::$alias)
            ->eloquentFilter($request);

        if ($request->get('is_archived')) {
            $branches = $branches->whereNotNull('archived_at');
        } else {
            $branches = $branches->whereNull('archived_at');
        }

        $branches = pagination($branches, $request->get('limit'));

        return new ApiCollection($branches);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ApiResource
     */
    public function store(StoreRequest $request)
    {
        $branch = new Branch;
        $branch->fill($request->all());
        $branch->save();

        return new ApiResource($branch);
    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $branch = Branch::from(Branch::getTableName() . ' as ' . Branch::$alias)
            ->eloquentFilter($request)
            ->where(Branch::$alias.'.id', $id)
            ->first();

        return new ApiResource($branch);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function update(UpdateRequest $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->fill($request->all());
        $branch->save();

        return new ApiResource($branch);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Branch::findOrFail($id)->delete();

        return response(null, 204);
    }
}
