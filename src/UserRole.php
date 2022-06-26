<?php

declare(strict_types=1);

namespace App;

enum UserRole: string
{
    case USER = 'USER_ROLE';
    case VERIFIED_USER = 'VERIFIED_USER_ROLE';
}