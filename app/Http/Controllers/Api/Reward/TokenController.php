<?php

namespace App\Http\Controllers\Api\Reward;

use App\Model\Reward\Token;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;

class TokenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $query = Token::where('user_id', auth()->user()->id);

        $points = pagination($query, $request->get('limit'));

        return new ApiCollection($points);
    }
}
