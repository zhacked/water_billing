<?php

namespace App\Providers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Example:
        // \App\Models\Post::class => \App\Policies\PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('admin-only', function ($user) {
            Log::info('Checking admin-only gate for user: ' . $user->role);
            return $user->role === 'admin';
        });
        Gate::define('admin-only', fn($user) => $user->role === 'admin');
        Gate::define('client-only', fn($user) => $user->role === 'client');
        Gate::define('staff-only', fn($user) => $user->role === 'staff');
        Gate::define('view-dashboard', fn($user) => in_array($user->role, ['admin', 'staff', 'client']));
    }
}
