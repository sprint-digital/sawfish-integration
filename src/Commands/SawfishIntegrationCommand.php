<?php

namespace SprintDigital\SawfishIntegration\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use SprintDigital\SawfishIntegration\Models\SawfishIntegration as SawfishIntegrationModel;
use SprintDigital\SawfishIntegration\SawfishIntegration;

class SawfishIntegrationCommand extends Command
{
    public $signature = 'sawfish:integrate';

    public $description = 'Migrate sawfish_integrations table and configure Sawfish credentials';

    public function handle(): int
    {
        $this->info('Setting up Sawfish Integration...');

        // Run the migration
        $this->info('Running migration for sawfish_integrations table...');
        try {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/create_sawfish_integration_table.php.stub'
            ]);
            $this->info('Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Check if credentials already exist
        $existingIntegration = SawfishIntegrationModel::first();
        if ($existingIntegration) {
            if (!$this->confirm('Sawfish integration already exists. Do you want to update the credentials?')) {
                $this->info('Integration setup cancelled.');
                return self::SUCCESS;
            }
        }

        // Prompt for credentials
        $this->info('Please provide your Sawfish credentials:');

        $clientId = $this->ask('Sawfish Client ID');
        if (empty($clientId)) {
            $this->error('Client ID is required!');
            return self::FAILURE;
        }

        $apiKey = $this->secret('Sawfish API Key');
        if (empty($apiKey)) {
            $this->error('API Key is required!');
            return self::FAILURE;
        }

        $accountId = $this->ask('Sawfish Account ID');

        // Store or update the credentials
        try {
            $integrationData = [
                'client_id' => $clientId,
                'api_key' => $apiKey,
                'sawfish_account_uuid' => $accountId,
                'expires_in' => 0, // Will be updated when token is generated
            ];

            if ($existingIntegration) {
                $existingIntegration->update($integrationData);
                $this->info('Sawfish integration credentials updated successfully!');
            } else {
                SawfishIntegrationModel::create($integrationData);
                $this->info('Sawfish integration credentials saved successfully!');
            }

            try {
                SawfishIntegration::generateToken();

                $this->info('Token generated successfully!');
            } catch (\Exception $e) {
                $this->error('Failed to generate token: ' . $e->getMessage());
                return self::FAILURE;
            }

            $this->info('Sawfish integration setup completed!');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to save credentials: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
