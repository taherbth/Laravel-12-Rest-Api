<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_number' => $this->customer_no,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email ?? 'N/A',
            'cell_phone' => $this->cell_phone,
            'city' => $this->city,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
 

