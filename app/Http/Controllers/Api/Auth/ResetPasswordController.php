<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Mail\ResetPasswordRequestMail;
use App\Http\Controllers\Controller;
use App\Model\Auth\PasswordReset;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function index(ResetPasswordRequest $request)
    {
        $passwordReset = new PasswordReset;
        $passwordReset->email = $request->get('email');
        $passwordReset->token = md5($request->get('email') . '' . now());
        $passwordReset->save();

        $url = 'https://' . env('TENANT_DOMAIN') . '/auth/reset-password?token=' . $passwordReset->token;

        Mail::to([$request->get('email')])->queue(new ResetPasswordRequestMail($url));
    }

    public function store(UpdatePasswordRequest $request)
    {
        $passwordReset = PasswordReset::where('email', $request->get('email'))->where('token', $request->get('token'))->first();

        if (! $passwordReset) {
            return response()->json([
                'code' => 422,
                'message' => 'Update password failed',
                'errors' => []
            ], 422);
        }

        DB::connection('mysql')->beginTransaction();

        $user = User::where('email', $request->get('email'))->first();
        $user->password = bcrypt($request->get('password'));
        $user->save();

        DB::connection('mysql')->table('password_resets')->where('token', $passwordReset->token)->delete();

        DB::connection('mysql')->commit();

        return response()->json([
           'message' => 'Update password success'
        ]);
    }
}
