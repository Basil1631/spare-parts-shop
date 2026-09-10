<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceService
{
    public function recordLogin(User $user, Request $request): void
    {
        $user->forceFill(['last_login_at' => now()])->save();

        AttendanceLog::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'worked_on' => now()->toDateString(),
            ],
            [
                'first_login_at' => now(),
                'ip_address' => $request->ip(),
            ]
        );
    }
}
