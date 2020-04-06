<?php

namespace App\Http\Controllers\Api\Plugin\PlayBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Plugin\PlayBook\Glossary;
use App\Http\Resources\ApiResource;
use App\Http\Resources\ApiCollection;
use App\Http\Requests\Plugin\PlayBook\Glossary\StoreGlossaryRequest as StoreRequest;
use App\Http\Requests\Plugin\PlayBook\Glossary\UpdateGlossaryRequest as UpdateRequest;

class GlossaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Glossary::filter($request);
        $glossaries = pagination($query, $request->limit ?: 10);

        return new ApiCollection($glossaries);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $glossary = Glossary::create($request->all());

        return new ApiResource($glossary);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Glossary $glossary)
    {
        return response()->json(compact('glossary'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Glossary $glossary)
    {
        $glossary->duplicateToHistory();
        $glossary->update($request->all());

        return response()->json(compact('glossary'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
