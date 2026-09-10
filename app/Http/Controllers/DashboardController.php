<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Enums\PaymentType;
use App\Models\AttendanceLog;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\Installment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = now()->toDateString();
        $thisWeekFrom = now()->startOfWeek()->toDateString();
        $lastWeekFrom = now()->subWeek()->startOfWeek()->toDateString();
        $lastWeekTo = now()->subWeek()->endOfWeek()->toDateString();
        $thisMonthFrom = now()->startOfMonth()->toDateString();
        $lastMonthFrom = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthTo = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $bills = Bill::query()->issued()->forUser($user);
        $todaySales = (clone $bills)->whereDate('billed_at', $today)->get();
        $weekSales = (clone $bills)->whereDate('billed_at', '>=', $thisWeekFrom)->sum('total_fils');
        $lastWeekSales = Bill::query()->issued()->forUser($user)
            ->whereDate('billed_at', '>=', $lastWeekFrom)->whereDate('billed_at', '<=', $lastWeekTo)->sum('total_fils');
        $monthSales = (clone $bills)->whereDate('billed_at', '>=', $thisMonthFrom)->sum('total_fils');
        $lastMonthSales = Bill::query()->issued()->forUser($user)
            ->whereDate('billed_at', '>=', $lastMonthFrom)->whereDate('billed_at', '<=', $lastMonthTo)->sum('total_fils');

        $branchProfits = collect();
        if ($user->isAdmin() || $user->isBranchManager()) {
            $branchQuery = Branch::query()->where('active', true);
            if (! $user->isAdmin()) {
                $branchQuery->whereKey($user->branch_id);
            }
            $branchProfits = $branchQuery->get()->map(function (Branch $branch) use ($thisMonthFrom) {
                $issued = Bill::query()->issued()->where('branch_id', $branch->id)->whereDate('billed_at', '>=', $thisMonthFrom)->with('items')->get();

                return [
                    'branch' => $branch,
                    'sales' => $issued->sum('total_fils'),
                    'profit' => $issued->sum(fn (Bill $b) => $b->profitFils()),
                ];
            });
        }

        $topRows = Bill::query()
            ->issued()
            ->forUser($user)
            ->whereDate('billed_at', '>=', $thisMonthFrom)
            ->selectRaw('created_by, sum(total_fils) as sales_fils')
            ->groupBy('created_by')
            ->orderByDesc('sales_fils')
            ->limit(8)
            ->get();
        $usersById = User::query()->whereIn('id', $topRows->pluck('created_by'))->get()->keyBy('id');
        $topStaff = $topRows->map(fn ($row) => [
            'user' => $usersById->get($row->created_by),
            'sales_fils' => (int) $row->sales_fils,
        ]);

        $presentToday = AttendanceLog::query()
            ->with('user.branch', 'user.roles')
            ->whereDate('worked_on', $today)
            ->when(! $user->isAdmin(), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('branch_id', $user->branch_id)))
            ->get();

        $lowStock = Product::query()->active()->lowStock()->orderBy('name')->limit(15)->get();
        $dueCredit = Bill::query()->issued()->forUser($user)
            ->where('payment_type', PaymentType::Credit->value)
            ->whereRaw('(total_fils - paid_fils - credited_fils) > 0')
            ->orderBy('credit_due_date')
            ->limit(10)
            ->get();
        $overdueInstallments = Installment::query()
            ->due()
            ->with('bill.garage')
            ->whereHas('bill', fn ($q) => $q->where('status', BillStatus::Issued->value))
            ->when(! $user->isAdmin() && $user->branch_id, fn ($q) => $q->whereHas('bill', fn ($b) => $b->where('branch_id', $user->branch_id)))
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'lowStock' => $lowStock,
            'lowStockCount' => Product::query()->active()->lowStock()->count(),
            'overdueInstallments' => $overdueInstallments,
            'dueCredit' => $dueCredit,
            'todayCount' => $todaySales->count(),
            'todayTotal' => $todaySales->sum('total_fils'),
            'todayCash' => $todaySales->where('payment_type', PaymentType::Cash)->sum('total_fils'),
            'weekSales' => $weekSales,
            'lastWeekSales' => $lastWeekSales,
            'monthSales' => $monthSales,
            'lastMonthSales' => $lastMonthSales,
            'branchProfits' => $branchProfits,
            'topStaff' => $topStaff,
            'presentToday' => $presentToday,
        ]);
    }
}
