<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\DeleteDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\UpdateDataTransfer;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Exporters\GrampsExporter;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataTransferController
{
    public function index(): JsonResponse
    {
        $transfers = DataTransfer::query()->latest()->paginate(25);

        return response()->json(['data' => $transfers->getCollection()->map(fn (DataTransfer $transfer): array => $this->resource($transfer))->values()->all(), 'meta' => ['current_page' => $transfers->currentPage(), 'per_page' => $transfers->perPage(), 'total' => $transfers->total()]]);
    }

    public function store(Request $request, CreateDataTransfer $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DataTransfer::STATUSES)],
            'format' => ['required', 'in:'.implode(',', DataTransfer::FORMATS)],
            'direction' => ['required', 'in:'.implode(',', DataTransfer::DIRECTIONS)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)], 201);
    }

    public function preview(Request $request, GenealogyImportService $service): JsonResponse
    {
        return response()->json(['data' => $service->preview($this->content($request))]);
    }

    public function import(Request $request, GenealogyImportService $service, CreateDataTransfer $create, UpdateDataTransfer $update): JsonResponse
    {
        $content = $this->content($request);
        $preview = $service->preview($content);
        $transfer = $create->execute([
            'name' => $request->input('name', 'Genealogy import'),
            'format' => $preview['format'],
            'direction' => 'import',
            'records_count' => $preview['people'],
            'status' => 'active',
        ]);

        try {
            $result = $service->import($content, false, $transfer);
        } catch (\Throwable $exception) {
            $update->execute($transfer, ['status' => 'failed', 'metadata' => ['error' => 'Import failed.']]);
            throw $exception;
        }

        return response()->json(['data' => $result, 'transfer' => $this->resource($transfer->refresh())], 201);
    }

    public function export(Request $request, GedcomExporter $gedcom, GrampsExporter $gramps): StreamedResponse
    {
        $format = $request->validate(['format' => ['sometimes', 'in:gedcom,gramps-xml']])['format'] ?? 'gedcom';
        $exporter = $format === 'gramps-xml' ? $gramps : $gedcom;

        $isGramps = $format === 'gramps-xml';
        $filename = $isGramps ? 'genealogy.gramps.xml' : 'genealogy.ged';
        $contentType = $isGramps ? 'application/xml; charset=UTF-8' : 'text/plain; charset=UTF-8';
        $people = Person::query()->latest()->get();
        $relationships = Relationship::query()->latest()->get();

        return response()->streamDownload(function () use ($exporter, $people, $relationships): void {
            echo $exporter->export($people, $relationships);
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function show(DataTransfer $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, DataTransfer $record, UpdateDataTransfer $update): JsonResponse
    {
        $record = $update->execute($record, $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:'.implode(',', DataTransfer::STATUSES)],
            'format' => ['sometimes', 'in:'.implode(',', DataTransfer::FORMATS)],
            'direction' => ['sometimes', 'in:'.implode(',', DataTransfer::DIRECTIONS)],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $this->resource($record)]);
    }

    public function destroy(DataTransfer $record, DeleteDataTransfer $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    private function content(Request $request): string
    {
        $request->validate([
            'content' => ['nullable', 'string', 'max:10485760', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:ged,gedcom,xml,txt', 'required_without:content'],
        ]);
        $file = $request->file('file');

        return $file instanceof UploadedFile ? (string) $file->get() : (string) $request->input('content');
    }

    /** @return array<string, mixed> */
    private function resource(DataTransfer $transfer): array
    {
        return ['id' => $transfer->getKey(), 'type' => 'genealogy-data-transfer', 'attributes' => [
            'name' => $transfer->name, 'format' => $transfer->format, 'direction' => $transfer->direction,
            'records_count' => $transfer->records_count, 'status' => $transfer->status, 'metadata' => $transfer->metadata,
            'created_at' => $transfer->created_at?->toISOString(), 'updated_at' => $transfer->updated_at?->toISOString(),
        ]];
    }
}
