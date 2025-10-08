<?php

namespace Database\Factories;

use App\Enums\SocialService;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service' => fake()->randomElement([
                SocialService::TWITTER,
                SocialService::FACEBOOK,
                SocialService::TELEGRAM,
            ]),
            'service_user_id' => fake()->numerify('########'),
            'username' => fake()->userName(),
            'access_token' => fake()->sha256(),
            'access_token_secret' => fake()->sha256(),
            'refresh_token' => null,
            'token_expires_at' => null,
            'scopes' => ['read', 'write'],
            'is_active' => true,
            'metadata' => [
                'name' => fake()->name(),
                'profile_image_url' => fake()->imageUrl(),
                'followers_count' => fake()->numberBetween(0, 10000),
                'friends_count' => fake()->numberBetween(0, 1000),
            ],
            'last_synced_at' => now(),
        ];
    }

    /**
     * Indicate that the account is for Twitter.
     */
    public function twitter(): static
    {
        return $this->state(fn (array $attributes) => [
            'service' => SocialService::TWITTER,
        ]);
    }

    /**
     * Indicate that the account is for Facebook.
     */
    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'service' => SocialService::FACEBOOK,
            'access_token_secret' => null,
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addDays(60),
        ]);
    }

    /**
     * Indicate that the account is for Telegram.
     */
    public function telegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'service' => SocialService::TELEGRAM,
            'access_token_secret' => null,
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addDays(60),
        ]);
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the token is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the token needs refresh.
     */
    public function needsRefresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => now()->addHours(12),
        ]);
    }
}
