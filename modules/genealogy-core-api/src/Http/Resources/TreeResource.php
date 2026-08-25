<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Api\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class TreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => 'genealogy-core-tree',
            'attributes' => [
                'name' => $this->name,
                'status' => $this->status,
                'description' => $this->description,
                'identifier' => $this->identifier,
                'terminology' => $this->terminology,
                'root_person_id' => $this->root_person_id,
                'is_public' => $this->is_public,
                'metadata' => $this->metadata,
                'stats' => $this->getStats(),
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
