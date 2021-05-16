<?php

namespace Denniseilander\PassportScopeRestriction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Denniseilander\PassportScopeRestriction\Models\Token;

class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Token::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'user_id' => null,
            'client_id' => null,
            'name' => null,
            'scopes' => [],
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ];
    }
}
