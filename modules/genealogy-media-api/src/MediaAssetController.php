<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Media\Actions\AnalyzeMediaFaces;
use Liberu\Genealogy\Media\Actions\CorrectMediaTranscription;
use Liberu\Genealogy\Media\Actions\CreateMediaAsset;
use Liberu\Genealogy\Media\Actions\CreateMediaLink;
use Liberu\Genealogy\Media\Actions\DeleteMediaAsset;
use Liberu\Genealogy\Media\Actions\ReviewMediaFaceTag;
use Liberu\Genealogy\Media\Actions\StoreMediaUpload;
use Liberu\Genealogy\Media\Actions\TranscribeMediaAsset;
use Liberu\Genealogy\Media\Actions\UpdateMediaAsset;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Models\MediaFaceTag;
use Liberu\Genealogy\Media\Queries\MediaLibrary;
use Symfony\Component\HttpFoundation\Response;

final class MediaAssetController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'kind' => ['sometimes', 'in:'.implode(',', MediaAsset::KINDS)],
            'public_only' => ['sometimes', 'boolean'],
        ]);
        $perPage = $values['page']['size'] ?? 25;
        $assets = MediaAsset::query()->when(isset($values['kind']), fn ($query) => $query->where('kind', $values['kind']))->when(($values['public_only'] ?? false), fn ($query) => $query->where('is_public', true))->latest()->paginate($perPage);

        return response()->json(['data' => $assets->getCollection()->map(fn (MediaAsset $asset): array => $this->resource($asset))->values()->all(), 'meta' => ['current_page' => $assets->currentPage(), 'per_page' => $assets->perPage(), 'total' => $assets->total()]]);
    }

    public function store(Request $request, CreateMediaAsset $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            ...$this->rules(),
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', MediaAsset::STATUSES)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function upload(Request $request, StoreMediaUpload $upload): JsonResponse
    {
        $values = $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'name' => ['sometimes', 'string', 'max:255'],
            'kind' => ['sometimes', 'in:'.implode(',', MediaAsset::KINDS)],
            'storage_disk' => ['sometimes', 'string', 'max:100'],
            'storage_directory' => ['sometimes', 'string', 'max:255'],
            'captured_at' => ['nullable', 'date'],
            'captured_place_id' => ['nullable', 'uuid'],
            'rights_holder' => ['nullable', 'string', 'max:255'],
            'rights_status' => ['sometimes', 'in:'.implode(',', MediaAsset::RIGHTS_STATUSES)],
            'is_public' => ['sometimes', 'boolean'],
            'preservation_metadata' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);
        $asset = $upload->execute($values['file'], $values);

        return response()->json(['data' => $this->resource($asset)], Response::HTTP_CREATED);
    }

    public function show(MediaAsset $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, MediaAsset $record, UpdateMediaAsset $update): JsonResponse
    {
        $record = $update->execute($record, $request->validate([
            ...$this->rules(true),
            'name' => ['sometimes', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)]);
    }

    public function destroy(MediaAsset $record, DeleteMediaAsset $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function library(Request $request, MediaLibrary $library): JsonResponse
    {
        $values = $request->validate(['kind' => ['nullable', 'in:'.implode(',', MediaAsset::KINDS)], 'q' => ['nullable', 'string', 'max:200'], 'public_only' => ['sometimes', 'boolean'], 'limit' => ['sometimes', 'integer', 'between:1,100']]);

        return response()->json(['data' => $library->execute($values['kind'] ?? null, $values['q'] ?? null, $values['public_only'] ?? false, $values['limit'] ?? 50)]);
    }

    public function link(Request $request, MediaAsset $record, CreateMediaLink $create): JsonResponse
    {
        $values = $request->validate(['linkable_type' => ['required', 'string', 'max:255'], 'linkable_id' => ['required', 'uuid'], 'role' => ['sometimes', 'string', 'max:100'], 'metadata' => ['nullable', 'array']]);
        $link = $create->execute([...$values, 'media_asset_id' => $record->getKey()]);

        return response()->json(['data' => ['id' => $link->getKey(), 'media_asset_id' => $link->media_asset_id, 'linkable_type' => $link->linkable_type, 'linkable_id' => $link->linkable_id, 'role' => $link->role]], 201);
    }

    public function analyzeFaces(MediaAsset $record, AnalyzeMediaFaces $analyze): JsonResponse
    {
        return response()->json(['data' => $analyze->execute($record)]);
    }

    public function transcribe(MediaAsset $record, TranscribeMediaAsset $transcribe): JsonResponse
    {
        return response()->json(['data' => $transcribe->execute($record)]);
    }

    public function correctTranscription(Request $request, MediaAsset $record, CorrectMediaTranscription $correct): JsonResponse
    {
        $values = $request->validate(['text' => ['required', 'string', 'max:2000000']]);
        $correction = $correct->execute($record, $values['text'], auth()->id() ? (string) auth()->id() : null);

        return response()->json(['data' => ['id' => $correction->getKey(), 'type' => 'genealogy-media-transcription-correction', 'attributes' => ['media_asset_id' => $correction->media_asset_id, 'original_text' => $correction->original_text, 'corrected_text' => $correction->corrected_text, 'actor_id' => $correction->actor_id, 'created_at' => $correction->created_at?->toISOString()]]], 201);
    }

    public function faceTags(MediaAsset $record): JsonResponse
    {
        return response()->json(['data' => $record->faceTags()->latest()->get()->map(fn (MediaFaceTag $tag): array => $this->faceTagResource($tag))->values()->all()]);
    }

    public function reviewFaceTag(Request $request, MediaFaceTag $tag, ReviewMediaFaceTag $review): JsonResponse
    {
        $values = $request->validate(['status' => ['required', 'in:confirmed,rejected'], 'person_id' => ['nullable', 'uuid']]);
        $tag = $review->execute($tag, $values['status'], $values['person_id'] ?? null, auth()->id() ? (string) auth()->id() : null);

        return response()->json(['data' => $this->faceTagResource($tag)]);
    }

    /** @return array<string, list<string>> */
    private function rules(bool $sometimes = false): array
    {
        $prefix = $sometimes ? 'sometimes' : 'sometimes';

        return [
            'kind' => [$prefix, 'in:'.implode(',', MediaAsset::KINDS)], 'storage_disk' => ['nullable', 'string', 'max:100'], 'storage_path' => ['nullable', 'string', 'max:2000'],
            'status' => [$prefix, 'in:'.implode(',', MediaAsset::STATUSES)],
            'mime_type' => ['nullable', 'string', 'max:255'], 'byte_size' => ['nullable', 'integer', 'min:0'], 'checksum' => ['nullable', 'string', 'max:128'],
            'captured_at' => ['nullable', 'date'], 'captured_place_id' => ['nullable', 'uuid'], 'transcription' => ['nullable', 'string'],
            'transcription_status' => ['sometimes', 'in:'.implode(',', MediaAsset::TRANSCRIPTION_STATUSES)], 'transcription_language' => ['nullable', 'string', 'max:16'],
            'rights_holder' => ['nullable', 'string', 'max:255'], 'rights_status' => ['nullable', 'in:'.implode(',', MediaAsset::RIGHTS_STATUSES)], 'license_url' => ['nullable', 'url', 'max:2000'],
            'rights_expires_at' => ['nullable', 'date'], 'is_public' => ['sometimes', 'boolean'], 'preservation_metadata' => ['nullable', 'array'],
        ];
    }

    /** @return array<string, mixed> */
    private function resource(MediaAsset $asset): array
    {
        return ['id' => $asset->getKey(), 'type' => 'genealogy-media-asset', 'attributes' => [
            'kind' => $asset->kind, 'name' => $asset->name, 'storage_disk' => $asset->storage_disk, 'storage_path' => $asset->storage_path,
            'mime_type' => $asset->mime_type, 'byte_size' => $asset->byte_size, 'checksum' => $asset->checksum, 'captured_at' => $asset->captured_at?->toISOString(),
            'captured_place_id' => $asset->captured_place_id, 'transcription' => $asset->transcription, 'transcription_status' => $asset->transcription_status,
            'transcription_language' => $asset->transcription_language, 'rights_holder' => $asset->rights_holder, 'rights_status' => $asset->rights_status,
            'license_url' => $asset->license_url, 'rights_expires_at' => $asset->rights_expires_at?->toDateString(), 'is_public' => $asset->is_public,
            'preservation_metadata' => $asset->preservation_metadata, 'status' => $asset->status, 'metadata' => $asset->metadata,
        ]];
    }

    /** @return array<string, mixed> */
    private function faceTagResource(MediaFaceTag $tag): array
    {
        return ['id' => $tag->getKey(), 'type' => 'genealogy-media-face-tag', 'attributes' => ['media_asset_id' => $tag->media_asset_id, 'person_id' => $tag->person_id, 'confidence' => $tag->confidence, 'bounding_box' => $tag->bounding_box, 'status' => $tag->status, 'confirmed_by' => $tag->confirmed_by, 'confirmed_at' => $tag->confirmed_at?->toISOString()]];
    }
}
