<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\PayrollAdjustment;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\CarbonInterface;

class PayrollService
{
    public function monthStart(?CarbonInterface $when = null): string
    {
        return ($when ?? now())->copy()->startOfMonth()->toDateString();
    }

    public function salesFils(User $user, string $from, string $to): int
    {
        return (int) Bill::query()
            ->issued()
            ->where('created_by', $user->id)
            ->whereDate('billed_at', '>=', $from)
            ->whereDate('billed_at', '<=', $to)
            ->sum('total_fils');
    }

    public function targetFils(User $user, string $periodStart): int
    {
        return (int) (SalesTarget::query()
            ->where('user_id', $user->id)
            ->whereDate('period_start', $periodStart)
            ->value('amount_fils') ?? 0);
    }

    /**
     * @return array{sales:int,target:int,extra:int,incentive:int,salary:int,cuttings:int,take_home:int,cuttings_notes:?string}
     */
    public function monthPack(User $user, ?CarbonInterface $when = null): array
    {
        $start = ($when ?? now())->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $sales = $this->salesFils($user, $start->toDateString(), $end->toDateString());
        $target = $this->targetFils($user, $start->toDateString());
        $extra = max(0, $sales - $target);
        $incentive = (int) round($extra * ((float) $user->incentive_percent) / 100);
        $salary = $user->monthly_salary_fils;
        $adj = PayrollAdjustment::query()
            ->where('user_id', $user->id)
            ->whereDate('period_start', $start->toDateString())
            ->first();
        $cuttings = (int) ($adj?->cuttings_fils ?? 0);

        return [
            'sales' => $sales,
            'target' => $target,
            'extra' => $extra,
            'incentive' => $incentive,
            'salary' => $salary,
            'cuttings' => $cuttings,
            'cuttings_notes' => $adj?->notes,
            'take_home' => max(0, $salary + $incentive - $cuttings),
        ];
    }
}
