<?php

namespace App\Http\Resources\Master\UserInvitation;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'user_email' => $this->user_email,
            'user_name' => $this->user_name,
            'joined' => $this->joined,
            'request_join_at' => $this->request_join_at,
        ];
    }
}
