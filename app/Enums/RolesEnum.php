<?php

namespace App\Enums;

enum RolesEnum
{
    case ADMIN;
    case USER;
    case COMPANY_ADMIN;
    case MANAGER;
    case VIEWER;

    public function value(): string
    {
        return match($this) {
            RolesEnum::ADMIN => 'Admin',
            RolesEnum::USER => 'User',
            RolesEnum::COMPANY_ADMIN => 'Company Admin',
            RolesEnum::MANAGER => 'Manager',
            RolesEnum::VIEWER => 'Viewer',
        };
    }
}
