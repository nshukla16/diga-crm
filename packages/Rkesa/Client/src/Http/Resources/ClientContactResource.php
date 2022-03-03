<?php

namespace Rkesa\Client\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientContactResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'can_be_readed' => $this->can_be_readed,
            'client_contact_phones' => ClientContactPhoneResource::collection($this->client_contact_phones),
            'client_contact_emails' => ClientContactEmailResource::collection($this->client_contact_emails),
        ];
    }
}
