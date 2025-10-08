<?php

namespace App\Enums;

enum AllowedEmailDomain: string
{
    case DREWROBERTS = 'drewroberts.com';
    case SWAMPAGENTS = 'swampagents.com';

    /**
     * Get all allowed email domains as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a given email domain is allowed.
     */
    public static function isAllowed(string $email): bool
    {
        $atPosition = strrchr($email, '@');
        
        if ($atPosition === false) {
            return false;
        }
        
        $domain = strtolower(substr($atPosition, 1));

        return in_array($domain, self::values(), true);
    }
}
