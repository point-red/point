<?php

namespace App\Http\Controllers\Api\Account;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Profile\UpdateProfileRequest;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Account\Profile\UpdateProfileRequest $request
     * @param  int                                                    $id
     *
     * @return void
     */
    public function update(UpdateProfileRequest $request, $id)
    {
        DB::beginTransaction();

        $user = User::findOrFail($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');
        $user->save();

        foreach ($user->projects as $project) {
            config()->set('database.connections.tenant.database', 'point_'.$project->code);
            DB::connection('tenant')->reconnect();
            DB::connection('tenant')->beginTransaction();

            $tenantUser = \App\Model\Master\User::where('id', $user->id)->first();
            if ($tenantUser) {
                if ($project->owner_id === $user->id) {
                    $tenantUser->name = $user->name;
                }
                $tenantUser->email = $user->email;
                $tenantUser->address = $user->address;
                $tenantUser->phone = $user->phone;
                $tenantUser->save();
            }
            DB::connection('tenant')->commit();
        }

        DB::commit();
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
