<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\User;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SalesVisitationTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $date = $request->get('date') == '' ? now() : $request->get('date');

        $query = SalesVisitationTarget::whereIn('date', function ($query) use ($date) {
            $query->selectRaw('max(date)')
                ->from(SalesVisitationTarget::getTableName())
                ->where('date', '<=', $date)
                ->groupBy('user_id');
        });

        $targets = User::leftJoinSub($query, 'query', function ($join) {
            $join->on('users.id', '=', 'query.user_id');
        })->select('query.id as id')
            ->addSelect('users.*')
            ->addSelect('users.name as name')
            ->addSelect('users.id as user_id')
            ->addSelect('query.date as date')
            ->addSelect('query.call as call')
            ->addSelect('query.effective_call as effective_call')
            ->addSelect('query.value as value')
            ->groupBy('user_id')
            ->get();

        return new ApiCollection($targets);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function store(Request $request)
    {
        $targets = $request->get('targets');
        $i = 0;
        foreach ($targets as $target) {
            if ($target['call'] != '' && $target['effective_call'] != '' && $target['value'] != '') {
                $newTarget = SalesVisitationTarget::where('user_id', $target['user_id'])
                    ->where('date', $target['date'])->first();
                if (!$newTarget) {
                    info(++$i . ' new');
                    $newTarget = new SalesVisitationTarget;
                } else {
                    info(++$i . ' old ' . $target['date']);
                }
                $newTarget->date = $target['date'];
                $newTarget->user_id = $target['user_id'];
                $newTarget->call = $target['call'];
                $newTarget->effective_call = $target['effective_call'];
                $newTarget->value = $target['value'];
                $newTarget->save();
            }
        }

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show($id)
    {
        $target = SalesVisitationTarget::findOrFail($id);

        return new ApiResource($target);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        $target = SalesVisitationTarget::findOrFail($id);
        $target->fill($request->all());
        $target->save();

        return new ApiResource($target);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $target = SalesVisitationTarget::findOrFail($id);

        $target->delete();

        return response()->json([], 204);
    }
}
