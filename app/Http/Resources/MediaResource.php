<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id'         => $this->id,
            'gallery_id' => $this->gallery_id,
            'name'       => $this->name,
            'file_path'  => $this->file_path,
            'mime_type'  => $this->mime_type,
            'file_type'  => $this->file_type,
            'file_size'  => $this->file_size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
 