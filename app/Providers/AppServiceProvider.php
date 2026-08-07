<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Policies\TenantPolicy;
use App\Services\Auth\AuthorizationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);

        Gate::define('permission', function ($user, string $permission, ?string $tenantId = null): bool {
            return app(AuthorizationService::class)->userHasPermission($user, $permission, $tenantId);
        });

        RateLimiter::for('api', function (Request $request) {
            $limit = config('examflow.rate_limit.api', 60);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });
    }
}
