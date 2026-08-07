<?php

use App\Http\Controllers\Api\V1\Academic\AcademicUnitController;
use App\Http\Controllers\Api\V1\Academic\AcademicUnitSubjectController;
use App\Http\Controllers\Api\V1\Academic\AcademicYearController;
use App\Http\Controllers\Api\V1\Academic\SubjectController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MembershipController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\Rbac\PermissionController;
use App\Http\Controllers\Api\V1\Rbac\RoleController;
use App\Http\Controllers\Api\V1\Rbac\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health/live', [HealthController::class, 'live'])->name('api.v1.health.live');
    Route::get('/health/ready', [HealthController::class, 'ready'])->name('api.v1.health.ready');
    Route::get('/health', [HealthController::class, 'ready'])->name('api.v1.health');

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [RegisterController::class, 'store'])
            ->middleware('throttle:register')
            ->name('api.v1.auth.register');

        Route::post('/login', [LoginController::class, 'login'])
            ->middleware('throttle:login')
            ->name('api.v1.auth.login');

        Route::post('/forgot-password', [PasswordController::class, 'forgot'])
            ->middleware('throttle:password')
            ->name('api.v1.auth.forgot-password');

        Route::post('/reset-password', [PasswordController::class, 'reset'])
            ->middleware('throttle:password')
            ->name('api.v1.auth.reset-password');

        Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
            ->name('api.v1.auth.email.verify');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', [LogoutController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/logout-all', [LogoutController::class, 'logoutAll'])->name('api.v1.auth.logout-all');
            Route::get('/me', [ProfileController::class, 'me'])->name('api.v1.auth.me');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('api.v1.auth.profile.update');
            Route::post('/change-password', [PasswordController::class, 'change'])->name('api.v1.auth.password.change');

            Route::get('/sessions', [LogoutController::class, 'sessions'])->name('api.v1.auth.sessions');
            Route::delete('/sessions/{session}', [LogoutController::class, 'revokeSession'])->name('api.v1.auth.sessions.revoke');

            Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:verification')
                ->name('api.v1.auth.email.resend');
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/organizations', [OrganizationController::class, 'index'])
            ->name('api.v1.organizations.index');

        Route::post('/organizations', [OrganizationController::class, 'store'])
            ->name('api.v1.organizations.store');

        Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])
            ->name('api.v1.organizations.show');

        Route::patch('/organizations/{organization}', [OrganizationController::class, 'update'])
            ->name('api.v1.organizations.update');

        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])
            ->name('api.v1.organizations.destroy');

        Route::get('/organizations/{organization}/members', [MembershipController::class, 'index'])
            ->name('api.v1.organizations.members.index');

        Route::post('/organizations/{organization}/members/invite', [MembershipController::class, 'invite'])
            ->name('api.v1.organizations.members.invite');

        Route::patch('/organizations/{organization}/members/{membership}', [MembershipController::class, 'update'])
            ->name('api.v1.organizations.members.update');

        Route::delete('/organizations/{organization}/members/{membership}', [MembershipController::class, 'destroy'])
            ->name('api.v1.organizations.members.destroy');

        Route::get('/organizations/{organization}/academic-years', [AcademicYearController::class, 'index'])
            ->middleware('permission:academic_year.view')
            ->name('api.v1.academic-years.index');

        Route::post('/organizations/{organization}/academic-years', [AcademicYearController::class, 'store'])
            ->middleware('permission:academic_year.create')
            ->name('api.v1.academic-years.store');

        Route::get('/organizations/{organization}/academic-years/{academicYear}', [AcademicYearController::class, 'show'])
            ->middleware('permission:academic_year.view')
            ->name('api.v1.academic-years.show');

        Route::patch('/organizations/{organization}/academic-years/{academicYear}', [AcademicYearController::class, 'update'])
            ->middleware('permission:academic_year.update')
            ->name('api.v1.academic-years.update');

        Route::delete('/organizations/{organization}/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])
            ->middleware('permission:academic_year.delete')
            ->name('api.v1.academic-years.destroy');

        Route::get('/organizations/{organization}/academic-units', [AcademicUnitController::class, 'index'])
            ->middleware('permission:academic_unit.view')
            ->name('api.v1.academic-units.index');

        Route::post('/organizations/{organization}/academic-units', [AcademicUnitController::class, 'store'])
            ->middleware('permission:academic_unit.create')
            ->name('api.v1.academic-units.store');

        Route::get('/organizations/{organization}/academic-units/{academicUnit}', [AcademicUnitController::class, 'show'])
            ->middleware('permission:academic_unit.view')
            ->name('api.v1.academic-units.show');

        Route::patch('/organizations/{organization}/academic-units/{academicUnit}', [AcademicUnitController::class, 'update'])
            ->middleware('permission:academic_unit.update')
            ->name('api.v1.academic-units.update');

        Route::delete('/organizations/{organization}/academic-units/{academicUnit}', [AcademicUnitController::class, 'destroy'])
            ->middleware('permission:academic_unit.delete')
            ->name('api.v1.academic-units.destroy');

        Route::get('/organizations/{organization}/subjects', [SubjectController::class, 'index'])
            ->middleware('permission:subject.view')
            ->name('api.v1.subjects.index');

        Route::post('/organizations/{organization}/subjects', [SubjectController::class, 'store'])
            ->middleware('permission:subject.create')
            ->name('api.v1.subjects.store');

        Route::get('/organizations/{organization}/subjects/{subject}', [SubjectController::class, 'show'])
            ->middleware('permission:subject.view')
            ->name('api.v1.subjects.show');

        Route::patch('/organizations/{organization}/subjects/{subject}', [SubjectController::class, 'update'])
            ->middleware('permission:subject.update')
            ->name('api.v1.subjects.update');

        Route::delete('/organizations/{organization}/subjects/{subject}', [SubjectController::class, 'destroy'])
            ->middleware('permission:subject.delete')
            ->name('api.v1.subjects.destroy');

        Route::get('/organizations/{organization}/academic-units/{academicUnit}/subjects', [AcademicUnitSubjectController::class, 'index'])
            ->middleware('permission:subject.view')
            ->name('api.v1.academic-units.subjects.index');

        Route::post('/organizations/{organization}/academic-units/{academicUnit}/subjects', [AcademicUnitSubjectController::class, 'store'])
            ->middleware('permission:subject.create')
            ->name('api.v1.academic-units.subjects.store');

        Route::delete('/organizations/{organization}/academic-units/{academicUnit}/subjects/{subject}', [AcademicUnitSubjectController::class, 'destroy'])
            ->middleware('permission:subject.delete')
            ->name('api.v1.academic-units.subjects.destroy');

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.permissions.index');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.index');

        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.store');

        Route::get('/roles/{role}', [RoleController::class, 'show'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.show');

        Route::patch('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.update');

        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.destroy');

        Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
            ->middleware('permission:roles.manage')
            ->name('api.v1.roles.permissions.sync');

        Route::post('/users/{user}/roles', [UserRoleController::class, 'store'])
            ->middleware('permission:users.manage')
            ->name('api.v1.users.roles.store');

        Route::delete('/users/{user}/roles/{role}', [UserRoleController::class, 'destroy'])
            ->middleware('permission:users.manage')
            ->name('api.v1.users.roles.destroy');
    });
});
