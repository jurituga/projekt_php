<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Freelancer = 'freelancer';
    case Company = 'company';
    case Admin = 'admin';
}
