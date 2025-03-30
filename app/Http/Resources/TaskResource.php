<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $array = parent::toArray($request);

         $assets = $this->assets->map(function ($asset) {
             return [
                 'name' => $asset->name,
                 'url' => Storage::url($asset->path),
             ];
         });

        $array['assets'] = $assets;

        return $array;
    }
}
