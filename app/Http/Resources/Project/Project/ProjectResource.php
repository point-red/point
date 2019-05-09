<?php

namespace App\Http\Resources\Project\Project;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
          'id' => $this->id,
          'code' => $this->code,
          'name' => $this->name,
          'group' => $this->group,
          'timezone' => $this->timezone,
          'address' => $this->address,
          'phone' => $this->phone,
          'vat_id_number' => $this->vat_id_number,
          'owner_id' => $this->owner_id,
          'joined' => $this->joined,
          'request_join_at' => $this->request_join_at,
          'invitation_code' => $this->invitation_code,
          'invitation_code_enabled' => $this->invitation_code_enabled,
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at,
        ];
    }
}
