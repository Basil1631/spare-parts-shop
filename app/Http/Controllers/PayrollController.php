<?php

namespace App\Http\Controllers;

use App\Models\PayrollAdjustment;
use App\Models\User;
use App\Services\PayrollService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request, PayrollService $payroll): View
    {
        abort_unless($request->user()->canSeeHr(), 403);
        $raw = $request->input('month');
        $month = $raw
            ? \Carbon\Carbon::parse(strlen((string) $raw) === 7 ? $raw.'-01' : $raw)->startOfMonth()
            : now()->startOfMonth();

        $staff = User::query()
            ->with('roles', 'branch')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sales', 'staff', 'branch_manager', 'purchase', 'accountant']))
            ->orderBy('name')
            ->get();

        $rows = $staff->map(function (User $user) use ($payroll, $month) {
            return ['user' => $user] + $payroll->monthPack($user, $month);
        });

        return view('payroll.index', compact('rows', 'month'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canSeeHr(), 403);
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'cuttings' => ['required', 'array'],
            'cuttings.*' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
        ]);
        $period = \Carbon\Carbon::parse($data['period_start'])->startOfMonth()->toDateString();

        foreach ($data['cuttings'] as $userId => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }
            $user = User::query()->findOrFail($userId);
            if (! $request->user()->isAdmin() && $user->branch_id !== $request->user()->branch_id) {
                abort(403);
            }
            PayrollAdjustment::query()->updateOrCreate(
                ['user_id' => $user->id, 'period_start' => $period],
                [
                    'cuttings_fils' => Money::toFils($amount),
                    'notes' => $data['notes'][$userId] ?? null,
                    'set_by' => $request->user()->id,
                ],
            );
        }

        return back()->with('status', 'Cuttings saved. Take-home = salary + incentive − cuttings.');
    }
}
