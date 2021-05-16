<?php

namespace Denniseilander\PassportScopeRestriction\Tests\Feature;

use Denniseilander\PassportScopeRestriction\PassportClientServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class PassportTestCase extends TestCase
{
    use RefreshDatabase;

    public const KEYS = __DIR__.'/keys';
    public const PUBLIC_KEY = self::KEYS.'/oauth-public.key';
    public const PRIVATE_KEY = self::KEYS.'/oauth-private.key';

    public string $allowed_scopes_column;

    public function setUp(): void
    {
        parent::setUp();

        $this->allowed_scopes_column = config('passport-scopes.allowed_scopes_column');

        $this->artisan('migrate:fresh');

        include_once __DIR__.'/../../database/migrations/add_allowed_scopes_column_to_oauth_clients_table.php.stub';

        (new \AddAllowedScopesColumnToOauthClientsTable())->up();

        Passport::routes();

        @unlink(self::PUBLIC_KEY);
        @unlink(self::PRIVATE_KEY);

        $this->artisan('passport:keys');
    }

    public function getEnvironmentSetUp($app): void
    {
        $config = $app->make(Repository::class);

        $config->set('auth.defaults.provider', 'users');

        if (($userClass = $this->getUserClass()) !== null) {
            $config->set('auth.providers.users.model', $userClass);
        }

        $config->set('auth.guards.api', ['driver' => 'passport', 'provider' => 'users']);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('passport.storage.database.connection', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            PassportClientServiceProvider::class,
            PassportServiceProvider::class,
        ];
    }

    /**
     * Get the Eloquent user model class name.
     *
     * @return string|null
     */
    protected function getUserClass(): ?string
    {
        return null;
    }
}
