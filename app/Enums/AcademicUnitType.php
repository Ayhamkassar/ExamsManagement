<?php

namespace App\Enums;

enum AcademicUnitType: string
{
    case Faculty = 'faculty';
    case Department = 'department';
    case Program = 'program';
    case Grade = 'grade';
    case ClassRoom = 'class';
    case Branch = 'branch';
    case CourseGroup = 'course_group';
    case ExaminationLevel = 'examination_level';
}
