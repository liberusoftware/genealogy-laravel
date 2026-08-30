<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Liberu\Genealogy\Timeline\Models\HistoricalEvent;

final class HistoricalEventService
{
    /** @return Collection<int, HistoricalEvent> */
    public function fetchForPeriod(string|\DateTimeInterface $start, string|\DateTimeInterface $end, ?string $country = null): Collection
    {
        return HistoricalEvent::query()
            ->betweenDates(CarbonImmutable::parse($start)->toDateString(), CarbonImmutable::parse($end)->toDateString())
            ->when($country !== null, fn ($query) => $query->where('country', $country))
            ->orderBy('event_date')
            ->get();
    }

    /** @return Collection<int, HistoricalEvent> */
    public function fetchForPerson(Model $person, int $bufferYears = 5): Collection
    {
        $birth = $person->getAttribute('birth_date');
        $death = $person->getAttribute('death_date');
        $start = $birth !== null ? CarbonImmutable::parse($birth)->subYears($bufferYears)->startOfYear() : null;
        $end = $death !== null ? CarbonImmutable::parse($death)->addYears($bufferYears)->endOfYear() : null;

        if ($start === null && $end !== null) {
            $start = $end->subYears(120)->startOfYear();
        }
        if ($start !== null && $end === null) {
            $end = $start->addYears(100)->endOfYear();
        }
        if ($start === null || $end === null) {
            $start = CarbonImmutable::now()->subYears(100)->startOfYear();
            $end = CarbonImmutable::now()->endOfYear();
        }

        return $this->fetchForPeriod($start, $end);
    }
}
