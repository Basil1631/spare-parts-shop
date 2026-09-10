<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->canManageStaff(), 403);
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $staff = User::query()
            ->with('roles', 'branch')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $presentToday = AttendanceLog::query()
            ->whereDate('worked_on', now()->toDateString())
            ->whereIn('user_id', $staff->pluck('id'))
            ->pluck('user_id')
            ->all();

        $days = max(1, Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1);
        $presentCounts = AttendanceLog::query()
            ->whereIn('user_id', $staff->pluck('id'))
            ->whereDate('worked_on', '>=', $from)
            ->whereDate('worked_on', '<=', $to)
            ->selectRaw('user_id, count(*) as present_days')
            ->groupBy('user_id')
            ->pluck('present_days', 'user_id');

        return view('attendance.index', compact('staff', 'presentToday', 'presentCounts', 'from', 'to', 'days'));
    }
}
