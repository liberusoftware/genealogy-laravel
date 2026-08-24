<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Timeline\Actions\CreateTimelineEvent;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class TimelineEventController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => TimelineEvent::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateTimelineEvent $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function show(TimelineEvent $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, TimelineEvent $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(TimelineEvent $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }
}
