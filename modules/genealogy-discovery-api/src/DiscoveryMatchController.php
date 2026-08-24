<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;

final class DiscoveryMatchController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => DiscoveryMatch::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateDiscoveryMatch $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(DiscoveryMatch $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, DiscoveryMatch $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(DiscoveryMatch $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
