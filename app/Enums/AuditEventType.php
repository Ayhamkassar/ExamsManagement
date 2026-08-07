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

    // Academic structure events
    case AcademicYearCreated = 'academic_year.created';
    case AcademicYearUpdated = 'academic_year.updated';
    case AcademicYearDeleted = 'academic_year.deleted';

    case AcademicUnitCreated = 'academic_unit.created';
    case AcademicUnitUpdated = 'academic_unit.updated';
    case AcademicUnitDeleted = 'academic_unit.deleted';

    case SubjectCreated = 'subject.created';
    case SubjectUpdated = 'subject.updated';
    case SubjectDeleted = 'subject.deleted';
    case SubjectAssigned = 'subject.assigned';
    case SubjectUnassigned = 'subject.unassigned';

    // Examination lifecycle events
    case ExaminationCycleCreated = 'examination_cycle.created';
    case ExaminationCycleUpdated = 'examination_cycle.updated';
    case ExaminationCycleDeleted = 'examination_cycle.deleted';
    case ExaminationCycleTransitioned = 'examination_cycle.transitioned';

    case ExaminationCreated = 'examination.created';
    case ExaminationUpdated = 'examination.updated';
    case ExaminationDeleted = 'examination.deleted';
    case ExaminationTransitioned = 'examination.transitioned';

    case ExaminationSessionCreated = 'examination_session.created';
    case ExaminationSessionUpdated = 'examination_session.updated';
    case ExaminationSessionDeleted = 'examination_session.deleted';
    case ExaminationSessionTransitioned = 'examination_session.transitioned';
}
