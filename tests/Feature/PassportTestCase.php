<?php

namespace Denniseilander\PassportScopeRestriction\Tests\Feature;

use Denniseilander\PassportScopeRestriction\PassportClientServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Workbench\App\Models\User;

abstract class PassportTestCase extends TestCase
{
    use LazilyRefreshDatabase;
    use WithWorkbench;

    public const KEYS = __DIR__.'/../keys';
    public const PUBLIC_KEY = self::KEYS.'/oauth-public.key';
    public const PRIVATE_KEY = self::KEYS.'/oauth-private.key';

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            Passport::loadKeysFrom(self::KEYS);

            @unlink(self::PUBLIC_KEY);
            @unlink(self::PRIVATE_KEY);

            $this->artisan('passport:keys');

            // Run migrations
            $this->runMigrations();
        });

        $this->beforeApplicationDestroyed(function () {
            @unlink(self::PUBLIC_KEY);
            @unlink(self::PRIVATE_KEY);
        });

        parent::setUp();
    }

    protected function defineEnvironment($app): void
    {
        $config = $app->make(Repository::class);

        $config->set([
            'auth.defaults.provider' => 'users',
            'auth.providers.users.model' => User::class,
            'auth.guards.api' => ['driver' => 'passport', 'provider' => 'users'],
            'database.default' => 'testing',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            PassportClientServiceProvider::class,
            PassportServiceProvider::class,
        ];
    }

    protected function runMigrations(): void
    {
        // Load Passport's migrations if they have been published
        if (file_exists(database_path('migrations'))) {
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../vendor/laravel/passport/database/migrations'));
        }

        // Manually include and run the migration that adds the allowed_scopes column
        require_once __DIR__.'/../../database/migrations/add_allowed_scopes_column_to_oauth_clients_table.php.stub';
        (new \AddAllowedScopesColumnToOauthClientsTable())->up();
    }
}
