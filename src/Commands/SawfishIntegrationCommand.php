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
        $this->info('Running migration tables...');
        try {
            $migrations = [
                'create_sawfish_integration_table.php',
                'create_sawfish_webhook_table.php',
            ];
            $currentDate = date('Y_m_d_His');

            foreach($migrations as $migration) {
                $stubPath = 'packages/sprint-digital/sawfish-integration/database/migrations/'. $migration.'.stub';
                $migrationPath = 'database/migrations/'.$currentDate.'_'.$migration;
                if (!file_exists($stubPath)) {
                    $this->error('Stub file not found: ' . $stubPath);
                    return self::FAILURE;
                }

                // check here if the migration path without the current date already exists
                // check here if file exists file name is like create_sawfish_integration_table.php

                // loop files under database/migrations check if filenam like $migration, copy the file if not exists
                $files = glob('database/migrations/*.php');
                $found = false;
                foreach($files as $file) {
                    if (strpos($file, $migration) !== false) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    if (!copy($stubPath, $migrationPath)) {
                        $this->error('Failed to copy stub file to migrations directory');
                        return self::FAILURE;
                    }
                }
            }

            Artisan::call('migrate');
            $this->info('Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getFile() . ':' . $e->getLine());
            return self::FAILURE;
        }

        // Check if credentials already exist
        $existingIntegration = SawfishIntegrationModel::latest()->first();
        if ($existingIntegration) {
            if (!$this->confirm('Sawfish integration already exists. Do you want to update the credentials?')) {
                $this->info('Integration setup cancelled.');
                return self::SUCCESS;
            }
        } else {
            $this->info('No Sawfish integration found. Creating new integration...');
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
        $webhookKey = $this->ask('Sawfish Webhook Key');

        // Store or update the credentials
        try {
            $integrationData = [
                'client_id' => $clientId,
                'api_key' => $apiKey,
                'sawfish_account_uuid' => $accountId ?? null,
                'webhook_key' => $webhookKey ?? null,
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
                $token = SawfishIntegration::ensureValidToken();
                if (isset($token['status']) && $token['status'] === 'ERROR') {
                    $this->error('Failed to generate token: ' . $token['message']);
                    return self::FAILURE;
                } else {
                    $this->info('Token generated successfully!');
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $this->error('Network error while generating token: ' . $e->getMessage());
                $this->error('Please check your internet connection and API endpoint.');
                return self::FAILURE;
            } catch (\Exception $e) {
                $this->error('Failed to generate token: ' . $e->getMessage());
                $this->error('Error details: ' . $e->getFile() . ':' . $e->getLine());
                return self::FAILURE;
            }

            $this->info('Sawfish integration setup completed!');
            return self::SUCCESS;

        } catch (\Illuminate\Database\QueryException $e) {
            $this->error('Database error while saving credentials: ' . $e->getMessage());
            $this->error('Please check your database connection and try again.');
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Failed to save credentials: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getFile() . ':' . $e->getLine());
            return self::FAILURE;
        }
    }
}
