<?php

namespace App\Http\Resources\Master\UserInvitation;

use App\Model\Project\ProjectUser;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserInvitationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (ProjectUser $userInvitation) {
            return new UserInvitationResource($userInvitation);
        });

        return parent::toArray($request);
    }
}
