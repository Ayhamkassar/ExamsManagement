<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case PendingVerification = 'pending_verification';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
            self::PendingVerification => 'Pending Verification',
        };
    }

    /**
     * Whether the account is permitted to authenticate.
     */
    public function canLogin(): bool
    {
        return match ($this) {
            self::Active => true,
            self::Suspended, self::Inactive, self::PendingVerification => false,
        };
    }
}
