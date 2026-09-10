<?php

namespace App\Http\Controllers;

use App\Enums\StaffRole;
use App\Models\SalesTarget;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageStaff(), 403);
        $raw = $request->input('period');
        $period = $raw
            ? \Carbon\Carbon::parse(strlen((string) $raw) === 7 ? $raw.'-01' : $raw)->startOfMonth()->toDateString()
            : now()->startOfMonth()->toDateString();

        $query = User::query()->with('roles', 'branch');
        if ($request->user()->isAdmin()) {
            $query->role(StaffRole::BranchManager->value);
        } else {
            $query->where('branch_id', $request->user()->branch_id)->role(StaffRole::Sales->value);
        }

        $people = $query->orderBy('name')->get();
        $targets = SalesTarget::query()
            ->whereIn('user_id', $people->pluck('id'))
            ->whereDate('period_start', $period)
            ->get()
            ->keyBy('user_id');

        return view('targets.index', compact('people', 'targets', 'period'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageStaff(), 403);
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'amount' => ['required', 'array'],
            'amount.*' => ['nullable', 'numeric', 'min:0'],
        ]);
        $period = \Carbon\Carbon::parse($data['period_start'])->startOfMonth()->toDateString();

        foreach ($data['amount'] as $userId => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }
            $user = User::query()->findOrFail($userId);
            if ($request->user()->isAdmin()) {
                abort_unless($user->isBranchManager(), 403);
            } else {
                abort_unless($user->branch_id === $request->user()->branch_id && $user->hasRole('sales'), 403);
            }
            SalesTarget::query()->updateOrCreate(
                ['user_id' => $user->id, 'period_start' => $period],
                ['amount_fils' => Money::toFils($amount), 'set_by' => $request->user()->id],
            );
        }

        return back()->with('status', 'Targets saved for this month.');
    }
}
