<?php

namespace App\Http\Resources\Master\User;

use App\Model\Master\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (User $user) {
            return new UserResource($user);
        });

        return parent::toArray($request);
    }
}
