<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // \App\Models\Model::class => \App\Policies\ModelPolicy::class,
    ];

    /**
     * Register any authentication/authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

    Gate::before(function ($user, $ability) {
        
        // Admin has access to everything EXCEPT client-only areas
        if ($user->hasRole('admin') && $ability !== 'client-only') {
            return true;
        }

        return null; // allow other Gate::define to run
    });

 
    // Example: shared access for plumber & cashier
    Gate::define('access-shared-page', fn($user) =>
        $user->hasAnyRole(['plumber', 'cashier'])
    );

    Gate::define('client-only', fn($user) =>
        $user->hasRole('client')
    );

    Gate::define('plumbing-only', fn($user) =>
        $user->hasRole('plumber')
    );

    Gate::define('cashier-only', fn($user) =>
        $user->hasRole('cashier')
    );

    Gate::define('view-dashboard', fn($user) =>
        $user->hasAnyRole(['admin', 'client', 'plumber', 'cashier'])
    );
    }
}
