<?php

namespace App\Enums;

enum SystemRole: string
{
    case SuperAdmin = 'super_admin';
    case OrganizationAdmin = 'organization_admin';
    case Examiner = 'examiner';
    case Reviewer = 'reviewer';
    case Student = 'student';
}
