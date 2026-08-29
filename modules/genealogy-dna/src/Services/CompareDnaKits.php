<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Services;

use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\GenealogyCore\TeamContext;

/** Compares two consented imported kits without exposing their raw contents. */
final class CompareDnaKits
{
    public function __construct(
        private readonly ParseDnaFile $parser,
        private readonly DnaFileVault $vault,
        private readonly AnalyzeDnaMatch $analyzer,
    ) {}

    /** @return array<string, mixed> */
    public function execute(DnaKit $kitA, DnaKit $kitB): array
    {
        $teamId = app(TeamContext::class)->require();
        if ((string) $kitA->team_id !== $teamId || (string) $kitB->team_id !== $teamId) {
            throw new InvalidArgumentException('Both DNA kits must belong to the active team.');
        }
        if ($kitA->getKey() === $kitB->getKey()) {
            throw new InvalidArgumentException('Two different DNA kits are required for comparison.');
        }
        if ($kitA->consent_status !== 'granted' || $kitB->consent_status !== 'granted') {
            throw new InvalidArgumentException('Both DNA kits must have active consent before comparison.');
        }
        if ($kitA->file_path === null || $kitB->file_path === null) {
            throw new InvalidArgumentException('Both DNA kits must contain an imported raw file.');
        }

        $mapA = $this->parser->parse($this->vault->read($kitA->file_path));
        $mapB = $this->parser->parse($this->vault->read($kitB->file_path));
        if ($mapA === [] || $mapB === []) {
            throw new InvalidArgumentException('Both DNA kit files must contain readable genotype data.');
        }

        return ['comparison_performed' => true, ...$this->analyzer->analyze($mapA, $mapB)];
    }
}
