<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Gate::define('void-bill', fn (User $user) => $user->hasAnyRole(['admin', 'branch_manager']));
        Gate::define('settings', fn (User $user) => $user->isAdmin());
        Gate::define('admin-only', fn (User $user) => $user->isAdmin());
    }
}
