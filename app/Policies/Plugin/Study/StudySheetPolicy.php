<?php

namespace App\Policies\Plugin\Study;

use App\User;
use App\Model\Plugin\Study\StudySheet;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudySheetPolicy
{
    use HandlesAuthorization;

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
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return mixed
     */
    public function view(User $user, StudySheet $sheet)
    {
        return $sheet->user_id === $user->id;
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
        return $tenantUser->hasPermissionTo('create study sheets', 'api');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return mixed
     */
    public function update(User $user, StudySheet $sheet)
    {
        $tenantUser = \App\Model\Master\User::find($user->id);
        return $tenantUser->hasPermissionTo('edit study sheets', 'api') &&
            $sheet->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return mixed
     */
    public function delete(User $user, StudySheet $sheet)
    {
        $tenantUser = \App\Model\Master\User::find($user->id);
        return $tenantUser->hasPermissionTo('delete study sheets', 'api') &&
            $sheet->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return mixed
     */
    public function restore(User $user, StudySheet $sheet)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Master\User  $user
     * @param  \App\Model\Plugin\Study\StudySheet  $sheet
     * @return mixed
     */
    public function forceDelete(User $user, StudySheet $sheet)
    {
        return false;
    }
}
