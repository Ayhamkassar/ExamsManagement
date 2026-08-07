<?php

namespace App\Enums;

enum ExaminationSessionStatus: string
{
    case Scheduled = 'scheduled';
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
