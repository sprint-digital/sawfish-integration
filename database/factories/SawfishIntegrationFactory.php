<?php

namespace SprintDigital\SawfishIntegration\Database\Factories;

use SprintDigital\SawfishIntegration\Models\SawfishIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\SprintDigital\SawfishIntegration\Models\SawfishIntegration>
 */
class SawfishIntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SawfishIntegration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => $this->faker->uuid(),
            'webhook_key' => $this->faker->sha256(),
            'api_key' => $this->faker->sha256(),
            'expires_in' => now()->addDays(30)->timestamp,
            'access_token' => $this->faker->sha256(),
            'refresh_token' => $this->faker->sha256(),
            'sawfish_account_uuid' => $this->faker->uuid(),
        ];
    }

    /**
     * Indicate that the integration is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_in' => now()->subDays(1)->timestamp,
        ]);
    }

    /**
     * Indicate that the integration has no expiration.
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_in' => null,
        ]);
    }
}
