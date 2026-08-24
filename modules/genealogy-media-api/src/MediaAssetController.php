<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Media\Actions\CreateMediaAsset;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class MediaAssetController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => MediaAsset::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateMediaAsset $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(MediaAsset $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, MediaAsset $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(MediaAsset $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
