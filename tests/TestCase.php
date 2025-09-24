<?php

namespace SprintDigital\SawfishIntegration\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use SprintDigital\SawfishIntegration\SawfishIntegrationServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SprintDigital\\SawfishIntegration\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SawfishIntegrationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Run migrations for testing
        $migration = include __DIR__ . '/../database/migrations/create_sawfish_integration_table.php.stub';
        $migration->up();
    }
}
