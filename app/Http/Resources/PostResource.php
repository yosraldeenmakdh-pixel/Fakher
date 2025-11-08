<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => $this->formatContent($this->content),
            'image' => $this->image ? asset('uploads/' . $this->image) : null,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }


    private function formatContent($htmlContent): string
    {
        $plainText = strip_tags($htmlContent);

        $plainText = html_entity_decode($plainText);

        $plainText = preg_replace('/\s+/', ' ', $plainText);

        return trim($plainText);
    }
}
