<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Master\User\StoreUserRequest;
use App\Http\Resources\ApiResource;
use App\Model\Project\ProjectUser;
use App\User;
use Illuminate\Support\Facades\DB;

class RegisterController extends ApiController
{
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        $emailConfirmationCode = substr(encrypt($request->input('email')), 0, 30);

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

        $project = ProjectUser::where('user_email', $user->email)->first();
        $project->user_id = $user->id;
        $project->save();

        DB::commit();
        return new ApiResource($user);
    }
}
