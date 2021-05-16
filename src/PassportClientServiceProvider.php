<?php

namespace Denniseilander\PassportScopeRestriction;

use Denniseilander\PassportScopeRestriction\Commands\SyncClientScopesCommand;
use Denniseilander\PassportScopeRestriction\Models\Client;
use Denniseilander\PassportScopeRestriction\Models\Token;
use Denniseilander\PassportScopeRestriction\Observers\TokenObserver;
use Laravel\Passport\Passport;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PassportClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-passport-scopes-restriction')
            ->hasConfigFile('passport-scopes')
            ->hasMigration('add_allowed_scopes_column_to_oauth_clients_table')
            ->hasCommand(SyncClientScopesCommand::class);
    }

    public function bootingPackage(): void
    {
        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);

        Passport::tokenModel()::observe(TokenObserver::class);
    }
}
