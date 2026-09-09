<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class InstallmentScheduler
{
    public function dueDates(CarbonInterface $billedAt, int $count, int $dayOfMonth): array
    {
        $dates = [];
        $cursor = CarbonImmutable::parse($billedAt->toDateString(), $billedAt->timezone);
        $first = $this->nextCollectionDayAfter($cursor, $dayOfMonth);
        $dates[] = $first->toDateString();

        for ($i = 1; $i < $count; $i++) {
            $first = $this->sameDayNextMonth($first, $dayOfMonth);
            $dates[] = $first->toDateString();
        }

        return $dates;
    }

    public function nextCollectionDayAfter(CarbonInterface $date, int $dayOfMonth): CarbonImmutable
    {
        $day = $this->clampDay($date->year, $date->month, $dayOfMonth);
        $candidate = CarbonImmutable::create($date->year, $date->month, $day, 0, 0, 0, $date->timezone);

        if ($candidate->toDateString() <= $date->toDateString()) {
            $nextMonth = $candidate->addMonthNoOverflow()->startOfMonth();
            $day = $this->clampDay($nextMonth->year, $nextMonth->month, $dayOfMonth);

            return CarbonImmutable::create($nextMonth->year, $nextMonth->month, $day, 0, 0, 0, $date->timezone);
        }

        return $candidate;
    }

    private function sameDayNextMonth(CarbonImmutable $date, int $dayOfMonth): CarbonImmutable
    {
        $next = $date->addMonthNoOverflow()->startOfMonth();
        $day = $this->clampDay($next->year, $next->month, $dayOfMonth);

        return CarbonImmutable::create($next->year, $next->month, $day, 0, 0, 0, $date->timezone);
    }

    private function clampDay(int $year, int $month, int $dayOfMonth): int
    {
        $max = CarbonImmutable::create($year, $month, 1)->daysInMonth;

        return min(max($dayOfMonth, 1), $max);
    }

    /**
     * @return list<int>
     */
    public function splitAmount(int $totalFils, int $count): array
    {
        $count = max(1, $count);
        $base = intdiv($totalFils, $count);
        $remainder = $totalFils % $count;
        $parts = array_fill(0, $count, $base);
        $parts[$count - 1] += $remainder;

        return $parts;
    }
}
