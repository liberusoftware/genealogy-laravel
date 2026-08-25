<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class TreeViewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->getKey(),
            'type' => 'genealogy-tree-viewer',
            'attributes' => [
                'name' => $this->name,
                'status' => $this->status,
                'root_person_id' => $this->root_person_id,
                'is_public' => $this->is_public,
                'metadata' => $this->metadata,
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
