<?php

namespace App\Http\Controllers\Api\MarketPlace;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use App\Model\Project\Project;
use App\Http\Controllers\Controller;
use App\Model\ProjectMarketPlace;
use App\Model\Marketplace\MarketplaceItem;

class MarketPlaceItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
         $item_list = MarketplaceItem::eloquentFilter($request)->join('project_marketplace', 'marketplace_items.project_id', '=', 'project_marketplace.project_id')
         ->where('project_marketplace.joined', 1)
         ->with('project')
         ->with('units')
         ->select('marketplace_items.*');

         $item_list = pagination($item_list, $request->get('limit'));

        return new ApiCollection($item_list);      


        //return new ApiCollection($purchaseOrders);
        //return null;
    }
}
