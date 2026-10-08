<?php
declare(strict_types=1);

namespace Nexo\Identity;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case PENDING = 'pending';
}
