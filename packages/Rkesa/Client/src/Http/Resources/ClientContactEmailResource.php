<?php

namespace Rkesa\Client\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientContactEmailResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'email' => $this->email,
        ];
    }
}
