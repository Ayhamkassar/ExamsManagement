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

    // Academic structure permissions
    case AcademicYearCreate = 'academic_year.create';
    case AcademicYearView = 'academic_year.view';
    case AcademicYearUpdate = 'academic_year.update';
    case AcademicYearDelete = 'academic_year.delete';

    case AcademicUnitCreate = 'academic_unit.create';
    case AcademicUnitView = 'academic_unit.view';
    case AcademicUnitUpdate = 'academic_unit.update';
    case AcademicUnitDelete = 'academic_unit.delete';

    case SubjectCreate = 'subject.create';
    case SubjectView = 'subject.view';
    case SubjectUpdate = 'subject.update';
    case SubjectDelete = 'subject.delete';

    // Examination lifecycle permissions
    case ExaminationCycleCreate = 'examination_cycle.create';
    case ExaminationCycleView = 'examination_cycle.view';
    case ExaminationCycleUpdate = 'examination_cycle.update';
    case ExaminationCycleDelete = 'examination_cycle.delete';
    case ExaminationCycleTransition = 'examination_cycle.transition';

    case ExaminationCreate = 'examination.create';
    case ExaminationView = 'examination.view';
    case ExaminationUpdate = 'examination.update';
    case ExaminationDelete = 'examination.delete';
    case ExaminationTransition = 'examination.transition';

    case ExaminationSessionCreate = 'examination_session.create';
    case ExaminationSessionView = 'examination_session.view';
    case ExaminationSessionUpdate = 'examination_session.update';
    case ExaminationSessionDelete = 'examination_session.delete';
    case ExaminationSessionTransition = 'examination_session.transition';
}
