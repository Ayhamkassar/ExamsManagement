<?php

namespace App\Enums;

enum SystemPermission: string
{
    case ManagePlatform = 'platform.manage';
    case ManageTenants = 'tenants.manage';
    case ManageOrganization = 'organization.manage';
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ViewAuditLogs = 'audit.view';
}
