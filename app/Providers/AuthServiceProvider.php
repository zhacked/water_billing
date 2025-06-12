<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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

        Gate::define('view-dashboard', fn($user) => in_array($user->role, ['admin', 'staff', 'eclient']));
        Gate::define('admin-only', fn($user) => $user->role === 'admin');
        Gate::define('client-only', fn($user) => $user->role === 'client');
        Gate::define('staff-only', fn($user) => $user->role === 'staff');
    }
}
