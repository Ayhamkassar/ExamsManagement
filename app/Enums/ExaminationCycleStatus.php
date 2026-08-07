<?php

namespace App\Enums;

enum ExaminationCycleStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';
}
