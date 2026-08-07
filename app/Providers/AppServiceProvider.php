<?php

namespace App\Providers;

use App\Models\AcademicUnit;
use App\Models\AcademicYear;
use App\Models\Membership;
use App\Models\PersonalAccessToken;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\Academic\AcademicUnitPolicy;
use App\Policies\Academic\AcademicYearPolicy;
use App\Policies\Academic\SubjectPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Services\Auth\AuthorizationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Membership::class, MembershipPolicy::class);
        Gate::policy(AcademicYear::class, AcademicYearPolicy::class);
        Gate::policy(AcademicUnit::class, AcademicUnitPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);

        Gate::define('permission', function ($user, string $permission, ?string $tenantId = null): bool {
            return app(AuthorizationService::class)->userHasPermission($user, $permission, $tenantId);
        });

        RateLimiter::for('api', function (Request $request) {
            $limit = config('examflow.rate_limit.api', 60);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute((int) config('examflow.auth.rate_limit.login', 5))
                ->by(($request->input('email') ?: '').'|'.$request->ip());
        });

        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute((int) config('examflow.auth.rate_limit.password', 3))
                ->by($request->ip().'|'.($request->input('email') ?: 'password'));
        });

        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinutes(15, (int) config('examflow.auth.rate_limit.verification', 3))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour((int) config('examflow.auth.rate_limit.register', 10))
                ->by($request->ip());
        });
    }
}
