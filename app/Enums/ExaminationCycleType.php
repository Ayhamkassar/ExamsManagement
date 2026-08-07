<?php

namespace App\Enums;

enum ExaminationCycleType: string
{
    case School = 'school';
    case University = 'university';
    case National = 'national';
    case Institutional = 'institutional';
    case Certification = 'certification';
    case Custom = 'custom';
}
