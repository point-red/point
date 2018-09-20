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
          'address' => $this->address,
          'vat_id_number' => $this->vat_id_number,
          'owner_id' => $this->owner_id,
          'invitation_code' => $this->invitation_code,
          'invitation_code_enabled' => $this->invitation_code_enabled ? true : false, // convert mysql tinyint into boolean
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at
        ];
    }
}
