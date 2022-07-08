<?php

namespace App\Policies\Plugin\Study;

use App\User;
use App\Model\Plugin\Study\StudySubject;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudySubjectPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Master\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return mixed
     */
    public function view(User $user, StudySubject $subject)
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Master\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        $tenantUser = \App\Model\Master\User::find($user->id);
        return $tenantUser->hasPermissionTo('create study subjects', 'api');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return mixed
     */
    public function update(User $user, StudySubject $subject)
    {
        $tenantUser = \App\Model\Master\User::find($user->id);
        return $tenantUser->hasPermissionTo('edit study subjects', 'api');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return mixed
     */
    public function delete(User $user, StudySubject $subject)
    {
        $tenantUser = \App\Model\Master\User::find($user->id);
        return $tenantUser->hasPermissionTo('delete study subjects', 'api');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return mixed
     */
    public function restore(User $user, StudySubject $subject)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySubject  $subject
     * @return mixed
     */
    public function forceDelete(User $user, StudySubject $subject)
    {
        return false;
    }
}
