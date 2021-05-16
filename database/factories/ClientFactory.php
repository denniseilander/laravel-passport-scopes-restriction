<?php

namespace Denniseilander\PassportScopeRestriction\Database\Factories;

use Denniseilander\PassportScopeRestriction\Models\Client;
use Illuminate\Support\Str;
use Laravel\Passport\Database\Factories\ClientFactory as PassportClientFactory;

class ClientFactory extends PassportClientFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->company,
            'secret' => Str::random(40),
            config('passport-scopes.allowed_scopes_column') => ["*"],
            'redirect' => $this->faker->url,
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ];
    }
}
