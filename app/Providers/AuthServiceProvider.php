<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gates para permisos específicos
        Gate::define('manage.users', function ($user) {
            return $user->hasPermissionTo('manage.users');
        });

        Gate::define('manage.projects', function ($user) {
            return $user->hasPermissionTo('manage.projects');
        });

        Gate::define('review.stage1', function ($user) {
            return $user->hasPermissionTo('review.stage1');
        });

        Gate::define('review.stage2', function ($user) {
            return $user->hasPermissionTo('review.stage2');
        });

        Gate::define('upload.documents', function ($user) {
            return $user->hasPermissionTo('upload.documents');
        });

        Gate::define('view.readonly', function ($user) {
            return $user->hasPermissionTo('view.readonly');
        });

        Gate::define('view.reports', function ($user) {
            return $user->hasPermissionTo('view.reports');
        });
    }
}
