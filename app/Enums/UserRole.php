<?php

namespace App\Enums;

enum UserRole:string
{
    case PROJECT_MANAGER='project_manager';
    case DEVELOPER='developer';
    case TESTER='tester';
}
