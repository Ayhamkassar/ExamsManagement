<?php

namespace App\Enums;

enum AuditEventType: string
{
    case OrganizationCreated = 'organization.created';
    case OrganizationUpdated = 'organization.updated';
    case OrganizationSuspended = 'organization.suspended';
    case OrganizationArchived = 'organization.archived';
    case OrganizationDeleted = 'organization.deleted';

    case MemberInvited = 'organization.member.invited';
    case MemberJoined = 'organization.member.joined';
    case MemberRemoved = 'organization.member.removed';
    case MemberRoleChanged = 'organization.member.role_changed';
    case MemberStatusChanged = 'organization.member.status_changed';
}
