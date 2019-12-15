<?php

namespace App\Http\Controllers\Api\Reward;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Reward\Token;
use Illuminate\Http\Request;

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

        $tokens = pagination($query, $request->get('limit'));

        return new ApiCollection($tokens);
    }
}
