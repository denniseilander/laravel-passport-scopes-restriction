<?php

namespace Denniseilander\PassportScopeRestriction\Commands;

use Denniseilander\PassportScopeRestriction\Models\Client;
use Denniseilander\PassportScopeRestriction\Models\Token;
use Illuminate\Console\Command;
use Laravel\Passport\Passport;

class SyncClientScopesCommand extends Command
{
    public $signature = 'passport:scopes-sync
                         {--keep-existing-scopes}';

    public $description = 'Sync the access token scopes with the current allowed client scopes.';

    public function handle(): int
    {
        $this->info("Synchronize access tokens scopes.");

        Passport::client()
            ->query()
            ->has('tokens')
            ->each(function (Client $client) {
                $this->info("Synchronizing {$client->tokens()->count()} access tokens of client $client->name");

                $allowedScopes = $client->getAllowedScopes();

                $client->tokens()->each(function (Token $token) use ($allowedScopes) {
                    $scopes = $token->scopes;

                    if (! $this->option('keep-existing-scopes')) {
                        // Scopes which doesn't exists in the allowed_scopes field are overwritten.
                        $scopes = array_intersect($token->scopes, $allowedScopes);
                    }

                    // Make sure there are no duplicate scopes
                    $scopes = array_unique(array_merge($allowedScopes, $scopes), SORT_REGULAR);

                    Token::withoutEvents(function () use ($token, $scopes) {
                        $token->update([
                            'scopes' => array_values(array_unique($scopes, SORT_REGULAR)),
                        ]);
                    });
                });
            });

        $this->info('Access tokens has been updated.');

        return 0;
    }
}
