<?php

namespace App\Enums;

enum AcademicYearStatus: string
{
    case Upcoming = 'upcoming';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
}
