<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserJar extends Resource
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
            'id' => (int) $this->id,
            'userId' => $this->whenLoaded('model', function () {
                return (int) $this->model->user_id;
            }),
            'fileName' => $this->file_name,
            'size' => (int) $this->size,
            'meta' => [
                'entrypoints' => $this->getCustomProperty('entrypoints', null),
            ],
            'createdAt' => $this->created_at->getTimestamp(),
        ];
    }
}
