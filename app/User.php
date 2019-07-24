<?php

namespace App;

use App\Model\Reward\Token;
use App\Model\Project\Project;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $connection = 'mysql';

    use Notifiable, HasApiTokens, HasRoles;

    /**
     * The users that belong to the project.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function findForPassport($username)
    {
        $field = filter_var($username, FILTER_VALIDATE_EMAIL)
            ? 'email' : 'name';

        return $this->where($field, $username)->first();
    }

    public function rewardTokens()
    {
        return $this->hasMany(Token::class);
    }
}
