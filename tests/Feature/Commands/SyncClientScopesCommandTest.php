<?php

namespace Denniseilander\PassportScopeRestriction\Tests\Feature\Commands;

use Denniseilander\PassportScopeRestriction\Commands\SyncClientScopesCommand;
use Denniseilander\PassportScopeRestriction\Database\Factories\ClientFactory;
use Denniseilander\PassportScopeRestriction\Database\Factories\TokenFactory;
use Denniseilander\PassportScopeRestriction\Models\Token;
use Denniseilander\PassportScopeRestriction\Tests\Feature\PassportTestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SyncClientScopesCommandTest extends PassportTestCase
{
    /**
     * @test
     */
    public function it_synchronizes_access_token_scopes_with_allowed_scopes(): void
    {
        $clients = ClientFactory::new()
            ->count(2)
            ->state(new Sequence(
                [$this->allowed_scopes_column => ['read-users']],
                [$this->allowed_scopes_column => ['write-users','delete-users']],
            ))
            ->asClientCredentials()
            ->create();

        $tokens = Token::withoutEvents(function () use ($clients) {
            return TokenFactory::new()
                ->count(2)
                ->state(new Sequence(
                    ['client_id' => $clients->first()->id],
                    ['client_id' => $clients->last()->id],
                ))
                ->create();
        });

        $this->artisan(SyncClientScopesCommand::class)->assertExitCode(0);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokens->first()->id,
            'client_id' => $clients->first()->id,
            'scopes' => json_encode($clients->first()->getAllowedScopes()),
        ]);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $tokens->last()->id,
            'client_id' => $clients->last()->id,
            'scopes' => json_encode($clients->last()->getAllowedScopes()),
        ]);
    }

    /**
     * @test
     */
    public function it_keeps_existing_scopes_when_synchronizing_access_token_scopes_with_allowed_scopes(): void
    {
        $client = ClientFactory::new()
            ->asClientCredentials()
            ->create([
                $this->allowed_scopes_column => ['read-users','delete-users'],
            ]);

        $token = Token::withoutEvents(function () use ($client) {
            return TokenFactory::new()
                ->create([
                    'client_id' => $client->id,
                    'scopes' => ['create-users'],
                ]);
        });

        $this->artisan(SyncClientScopesCommand::class, [
            '--keep-existing-scopes' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $token->id,
            'client_id' => $client->id,
            'scopes' => json_encode([
                'read-users',
                'delete-users',
                'create-users',
            ]),
        ]);
    }
}
