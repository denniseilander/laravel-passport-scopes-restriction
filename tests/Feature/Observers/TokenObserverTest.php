<?php

namespace Denniseilander\PassportScopeRestriction\Tests\Feature\Observers;

use Denniseilander\PassportScopeRestriction\Tests\Feature\PassportTestCase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Database\Factories\ClientFactory;
use Laravel\Passport\Passport;

class TokenObserverTest extends PassportTestCase
{
    /**
     * @test
     */
    public function it_assigns_allowed_scopes_to_access_token(): void
    {
        $client = ClientFactory::new()->asClientCredentials()->create([
            config('passport-scopes.allowed_scopes_column') => '["read-users", "write-users"]',
        ]);

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'client_id' => $client->id,
            'scopes' => '["read-users","write-users"]',
        ]);
    }

    /**
     * @test
     */
    public function it_only_assigns_allowed_scopes_given_in_request(): void
    {
        Config::set('passport-scopes.enable_requesting_scopes', true);

        Passport::tokensCan([
            'scope-1' => 'Scope 1',
            'scope-2' => 'Scope 2',
            'scope-3' => 'Scope 3',
        ]);

        $client = ClientFactory::new()->asClientCredentials()->create([
            config('passport-scopes.allowed_scopes_column') => '["scope-1", "scope-2", "scope-3"]',
        ]);

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => 'scope-3',
        ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'client_id' => $client->id,
            'scopes' => '["scope-3"]',
        ]);
    }

    /**
     * @test
     */
    public function it_doesnt_assign_scopes_when_allowed_scopes_is_null(): void
    {
        $client = ClientFactory::new()->asClientCredentials()->create([
            config('passport-scopes.allowed_scopes_column') => null,
        ]);

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'client_id' => $client->id,
            'scopes' => '[]',
        ]);
    }

    /**
     * @test
     */
    public function it_ignores_scopes_in_request_when_this_option_is_disabled(): void
    {
        Config::set('passport-scopes.enable_requesting_scopes', false);

        Passport::tokensCan([
            'scope-1' => 'Scope 1',
            'scope-2' => 'Scope 2',
            'scope-3' => 'Scope 3',
        ]);

        $client = ClientFactory::new()->asClientCredentials()->create([
            config('passport-scopes.allowed_scopes_column') => '["scope-1", "scope-2", "scope-3"]',
        ]);

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => 'scope-3',
        ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'client_id' => $client->id,
            'scopes' => '["scope-1","scope-2","scope-3"]',
        ]);
    }

    /**
     * @test
     * @dataProvider invalidScopesDataProvider
     */
    public function it_throws_exception_on_invalid_scopes(
        $config,
        $tokensCan,
        $allowedScopes,
        $requestedScopes,
        $assertion
    ): void {
        Config::set('passport-scopes.enable_requesting_scopes', $config);

        Passport::tokensCan($tokensCan);

        $client = ClientFactory::new()->asClientCredentials()->create([
            $this->allowed_scopes_column => $allowedScopes,
        ]);

        $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => $requestedScopes,
        ])->assertStatus($assertion['status'])->assertJson($assertion['json']);
    }

    public function invalidScopesDataProvider(): array
    {
        return [
            'invalid_requested_scope' => [
                'config' => true,
                'tokens_can' => [
                    'read-users' => 'Read users.',
                ],
                'allowed_scopes' => '["read-users"]',
                'requested_scopes' => 'read-users delete-users',
                'assertion' => [
                    'status' => 400,
                    'json' => [
                        'error' => 'invalid_scope',
                        'error_description' => 'The requested scope is invalid, unknown, or malformed',
                        'hint' => 'Check the `delete-users` scope',
                        'message' => 'The requested scope is invalid, unknown, or malformed',
                    ],
                ],
            ],
            'invalid_application_scope' => [
                'config' => false,
                'tokens_can' => [
                    'read-users' => 'Read users.',
                    'delete-users' => 'Delete users.',
                ],
                'allowed_scopes' => '["read-users","delete-users"]',
                'requested_scopes' => 'edit-users',
                'assertion' => [
                    'status' => 400,
                    'json' => [
                        'error' => 'invalid_scope',
                        'error_description' => 'The requested scope is invalid, unknown, or malformed',
                        'hint' => 'Check the `edit-users` scope',
                        'message' => 'The requested scope is invalid, unknown, or malformed',
                    ],
                ],
            ],
        ];
    }
}
