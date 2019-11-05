<?php

namespace App\Http\Controllers\Api\Reward;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Reward\TokenGenerator;
use Illuminate\Http\Request;

class TokenGeneratorController extends Controller
{
    public function index(Request $request)
    {
        $tokenGenerators = TokenGenerator::all();

        return new ApiCollection($tokenGenerators);
    }

    public function show(Request $request, $id)
    {
        $tokenGenerator = TokenGenerator::findOrFail($id);

        return new ApiResource($tokenGenerator);
    }

    public function update(Request $request)
    {
        $tokenGenerator = TokenGenerator::first();
        $tokenGenerator->update($request->all());

        return $tokenGenerator;
    }
}
