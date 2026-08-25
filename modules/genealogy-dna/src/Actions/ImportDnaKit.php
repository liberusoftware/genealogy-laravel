<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Services\DnaFileValidator;
use Liberu\Genealogy\Dna\Services\DnaFileVault;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ImportDnaKit
{
    public function __construct(
        private readonly DnaFileValidator $validator,
        private readonly DnaFileVault $vault,
    ) {}

    public function execute(string $content, array $attributes): DnaKit
    {
        $validation = $this->validator->validate($content);
        if (! $validation['valid']) {
            throw ValidationException::withMessages(['content' => $validation['error'] ?? 'The DNA file is invalid.']);
        }

        $values = Arr::only($attributes, ['name', 'provider', 'provider_id', 'external_id', 'person_id', 'test_type', 'consent_status', 'status', 'metadata']);
        $teamId = app(TeamContext::class)->require();
        $filePath = 'genealogy-dna/'.$teamId.'/'.Str::uuid().'.dna';
        $this->vault->store($content, $filePath);

        try {
            return app(CreateDnaKit::class)->execute([
                ...$values,
                'file_path' => $filePath,
                'file_hash' => hash('sha256', $content),
                'file_format' => $validation['format'],
                'snp_count' => $validation['snp_count'],
            ]);
        } catch (\Throwable $exception) {
            $this->vault->delete($filePath);
            throw $exception;
        }
    }
}
