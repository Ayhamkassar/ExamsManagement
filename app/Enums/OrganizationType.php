<?php

namespace App\Enums;

enum OrganizationType: string
{
    case School = 'school';
    case University = 'university';
    case TrainingCenter = 'training_center';
    case ExaminationAuthority = 'examination_authority';
    case Government = 'government';
}
