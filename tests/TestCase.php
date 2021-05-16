<?php

namespace Denniseilander\PassportScopeRestriction\Tests;

use Denniseilander\PassportScopeRestriction\PassportClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PassportClientServiceProvider::class,
        ];
    }
}
