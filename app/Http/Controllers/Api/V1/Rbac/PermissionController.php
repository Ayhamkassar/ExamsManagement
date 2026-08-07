<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('slug')->get();

        return ApiResponse::success(['permissions' => PermissionResource::collection($permissions)]);
    }
}
