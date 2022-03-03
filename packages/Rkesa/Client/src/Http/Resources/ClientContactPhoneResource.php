<?php

namespace Rkesa\Client\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientContactPhoneResource extends JsonResource
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
            'phone_number' => $this->phone_number,
        ];
    }
}
