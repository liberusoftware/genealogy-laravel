<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataTransferController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => DataTransfer::query()->latest()->paginate()]);
    }

    public function store(Request $request, CreateDataTransfer $create): JsonResponse
    {
        $record = $create->execute($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'format' => ['required', 'in:gedcom,gramps-xml'],
            'direction' => ['required', 'in:import,export'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record], 201);
    }

    public function preview(Request $request, GenealogyImportService $service): JsonResponse
    {
        return response()->json(['data' => $service->preview($this->content($request))]);
    }

    public function import(Request $request, GenealogyImportService $service, CreateDataTransfer $create): JsonResponse
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

        return response()->json(['data' => $service->import($content, false, $transfer)], 201);
    }

    public function export(GedcomExporter $exporter): StreamedResponse
    {
        return response()->streamDownload(function () use ($exporter): void {
            echo $exporter->export(Person::query()->latest()->get());
        }, 'genealogy.ged', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function show(DataTransfer $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function update(Request $request, DataTransfer $record): JsonResponse
    {
        $record->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:50'],
            'format' => ['sometimes', 'in:gedcom,gramps-xml'],
            'direction' => ['sometimes', 'in:import,export'],
            'metadata' => ['nullable', 'array'],
        ]));

        return response()->json(['data' => $record->refresh()]);
    }

    public function destroy(DataTransfer $record): JsonResponse
    {
        $record->delete();

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
}
