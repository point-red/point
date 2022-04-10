<?php

namespace App;

use App\Model\Project\Project;
use App\Model\Reward\Token;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 */
class User extends Authenticatable
{
    protected $connection = 'mysql';

    protected $appends = ['full_name'];

    use Notifiable, HasApiTokens, HasRoles;

    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

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
