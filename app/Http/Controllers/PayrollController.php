<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __invoke(Request $request, PayrollService $payroll): View
    {
        abort_unless($request->user()->canManageStaff(), 403);
        $raw = $request->input('month');
        $month = $raw
            ? \Carbon\Carbon::parse(strlen((string) $raw) === 7 ? $raw.'-01' : $raw)->startOfMonth()
            : now()->startOfMonth();

        $staff = User::query()
            ->with('roles', 'branch')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sales', 'branch_manager', 'purchase', 'accountant']))
            ->orderBy('name')
            ->get();

        $rows = $staff->map(function (User $user) use ($payroll, $month) {
            return ['user' => $user] + $payroll->monthPack($user, $month);
        });

        return view('payroll.index', compact('rows', 'month'));
    }
}
