<?php

namespace SprintDigital\SawfishIntegration;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SprintDigital\SawfishIntegration\Commands\SawfishIntegrationCommand;

class SawfishIntegrationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sawfish-integration')
            ->hasConfigFile()
            // ->hasViews()
            ->hasMigration('create_sawfish_integration_table')
            ->hasCommand(SawfishIntegrationCommand::class);
    }
}
