<?php

namespace Tests\Unit;

use App\Services\InstallmentScheduler;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class InstallmentSchedulerTest extends TestCase
{
    public function test_next_third_is_strictly_after_bill_date(): void
    {
        $scheduler = new InstallmentScheduler;
        $dates = $scheduler->dueDates(CarbonImmutable::parse('2026-01-03', 'Asia/Dubai'), 3, 3);

        $this->assertSame(['2026-02-03', '2026-03-03', '2026-04-03'], $dates);
    }

    public function test_bill_before_third_uses_this_month(): void
    {
        $scheduler = new InstallmentScheduler;
        $dates = $scheduler->dueDates(CarbonImmutable::parse('2026-01-02', 'Asia/Dubai'), 2, 3);

        $this->assertSame(['2026-01-03', '2026-02-03'], $dates);
    }

    public function test_split_puts_remainder_on_last(): void
    {
        $parts = (new InstallmentScheduler)->splitAmount(1000, 3);

        $this->assertSame([333, 333, 334], $parts);
    }
}
