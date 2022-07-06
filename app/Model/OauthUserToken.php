<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OauthUserToken extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'provider',
    ];
}
