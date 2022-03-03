<?php

namespace Rkesa\Calendar\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Rkesa\Client\Http\Resources\ClientContactResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        $this->load(['service', 'project']);
        return [
            'id' => $this->id,
            'event_type_id' => $this->event_type_id,
            'description' => $this->description,
            'start' => (string) $this->start,
            'end' => (string) $this->finish,
            'user_id' => $this->user_id, // responsible
            'client_contact' => new ClientContactResource($this->client_contact),
            'done' => $this->done,
            'project_url' => $this->url,
            'project_id' => $this->project_id,
            'project' => $this->project ? [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ] : null,
            'service_id' => $this->service_id,
            'service' => $this->service ? [
                'address' => $this->service->address,
                'id' => $this->service->id,
                'estimate_number' => $this->service->estimate_number,
                'additional' => $this->service->additional,
                'name' => $this->service->name,
                'estimate_summ' => $this->service->estimate_summ,
                'service_type_id' => $this->service->service_type_id,
            ] : null,
//            'event_group_id' => $this->event_group_id, CalendarExtended feature
        ];
    }
}
