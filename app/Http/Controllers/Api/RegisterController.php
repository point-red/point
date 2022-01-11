<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Master\User\StoreUserRequest;
use App\Http\Resources\ApiResource;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Model\Project\ProjectUser;

class RegisterController extends ApiController
{
    public function store(StoreUserRequest $request)
    {
        $emailConfirmationCode = substr(encrypt($request->input('email')), 0, 30);

        DB::beginTransaction();

        $user = new User;
        $user->name = $request->username;
        $user->email = $request->email;
        $user->email_confirmation_code = $emailConfirmationCode;
        $user->password = bcrypt($request->password);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->save();

        $projects = ProjectUser::where('user_email', $user->email)->get();
        foreach($projects as $project) {
            $project->user_id = $user->id;
            $project->save();
        }

        DB::commit();

        return new ApiResource($user);
    }
}
