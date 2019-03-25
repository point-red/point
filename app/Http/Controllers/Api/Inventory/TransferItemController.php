<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Model\Master\User;
use Illuminate\Http\Request;
use App\Model\Form;
use App\Model\Inventory\Transfer\Transfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\ApiResource;
use App\Http\Requests\Inventory\Transfer\TransferItemRequest;
use App\Http\Requests\Project\Project\DeleteProjectRequest;
use App\Http\Requests\Project\Project\UpdateProjectRequest;

class TransferItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $transfers = Form::select('forms.id', 'forms.date', 'forms.number', 'forms.approved', 'forms.canceled', 'forms.done')
            ->where('formable_type', 'transfer');
        // dd($transfers->toSql());

        $transfers = pagination($transfers, $request->input('limit'));

        return new ApiCollection($transfers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TransferItemRequest $request
     * @return \App\Http\Resources\ApiResource
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $transfer = Transfer::create($request->all());
            $transfer
                ->load('form')
                ->load('items.item');

            return new ApiResource($transfer);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function show($id)
    {
        return new ProjectResource(Project::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Project\Project\UpdateProjectRequest $request
     * @param  int                                                    $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function update(UpdateProjectRequest $request, $id)
    {
        // Update tenant database name in configuration
        $project = Project::findOrFail($id);
        $project->name = $request->get('name');
        $project->address = $request->get('address');
        $project->phone = $request->get('phone');
        $project->vat_id_number = $request->get('vat_id_number');
        $project->invitation_code = $request->get('invitation_code');
        $project->invitation_code_enabled = $request->get('invitation_code_enabled');
        $project->save();

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\Project\Project\DeleteProjectRequest $request
     * @param  int                                                    $id
     *
     * @return \App\Http\Resources\Project\Project\ProjectResource
     */
    public function destroy(DeleteProjectRequest $request, $id)
    {
        $project = Project::findOrFail($id);

        $project->delete();

        // Delete database tenant
        Artisan::call('tenant:database:delete', [
            'db_name' => 'point_'.strtolower($project->code),
        ]);

        return new ProjectResource($project);
    }
}
