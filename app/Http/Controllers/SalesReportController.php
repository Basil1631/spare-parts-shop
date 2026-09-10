<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'branch_manager', 'sales', 'staff']), 403);

        $range = $request->string('range')->toString() ?: 'day';
        [$from, $to] = match ($range) {
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            default => [now()->toDateString(), now()->toDateString()],
        };

        $staffQuery = User::query()->role(['sales', 'staff', 'branch_manager']);
        if ($request->user()->isAdmin()) {
            // all
        } elseif ($request->user()->isBranchManager()) {
            $staffQuery->where('branch_id', $request->user()->branch_id);
        } else {
            $staffQuery->whereKey($request->user()->id);
        }

        $staff = $staffQuery->orderBy('name')->get();
        $totals = Bill::query()
            ->issued()
            ->whereDate('billed_at', '>=', $from)
            ->whereDate('billed_at', '<=', $to)
            ->when(! $request->user()->isAdmin() && $request->user()->branch_id, fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->when($request->user()->hasRole('sales') && ! $request->user()->isBranchManager(), fn ($q) => $q->where('created_by', $request->user()->id))
            ->selectRaw('created_by, sum(total_fils) as sales_fils, count(*) as bills_count')
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        return view('reports.sales', compact('staff', 'totals', 'range', 'from', 'to'));
    }
}
