<?php

namespace App\Enums;

enum SocialService: string
{
    case TWITTER = 'twitter';
    case FACEBOOK = 'facebook';
    case TELEGRAM = 'telegram';

    /**
     * Get all available social services.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the service.
     */
    public function label(): string
    {
        return match ($this) {
            self::TWITTER => 'Twitter (X)',
            self::FACEBOOK => 'Facebook',
            self::TELEGRAM => 'Telegram',
        };
    }

    /**
     * Get the icon for the service (you can use heroicons or custom icons).
     */
    public function icon(): string
    {
        return match ($this) {
            self::TWITTER => 'heroicon-o-x-mark',
            self::FACEBOOK => 'heroicon-o-globe-alt',
            self::TELEGRAM => 'heroicon-o-paper-airplane',
        };
    }

    /**
     * Check if this service uses OAuth 1.0a.
     */
    public function usesOAuth1(): bool
    {
        return $this === self::TWITTER;
    }

    /**
     * Check if this service uses OAuth 2.0.
     */
    public function usesOAuth2(): bool
    {
        return in_array($this, [self::FACEBOOK, self::TELEGRAM], true);
    }
}
