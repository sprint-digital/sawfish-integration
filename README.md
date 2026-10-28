# This is a package for Sawfish accounting API integration

## Installation

You can install the package via composer: (This is pending as it's not added to Packagist yet)

```bash
composer require sprint-digital/sawfish-integration
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sawfish-integration-config"
```

This is the contents of the published config file:

```php
return [
    'api_url' => env('SAWFISH_API_URL'),
];
```
The default SAWFISH_API_URL is https://api.sawfish.com.au/api/v2/accounting, but if you want to use the staging URL publish the config then add this to your .env SAWFISH_API_URL=https://api.sawfish.2mm.io/api/v2/accounting

## Testing

```bash
composer test
```

## Setup

This is to setup sawfish integration, this creates the table and asks for the sawfish credentials

```bash
php artisan sawfish:integrate
```

### Zero-downtime deployments (e.g. Laravel Forge)

Run the command from the active release, not the site root (the root has no `artisan`):

```bash
cd /home/forge/{site}/current
php artisan sawfish:integrate
```

In production the command asks for confirmation before running its migrations. Pass `--force` to skip the prompt (e.g. in deploy scripts). Migration files copied on the server only exist in that release. Publish and commit them locally (`php artisan vendor:publish --tag=sawfish-integration-migrations`) if you want them in your repository. Re-running the command after a later deploy is safe, because the migrations skip tables that already exist.

## Usage

```php
use SprintDigital\SawfishIntegration\SawfishIntegration;

// Token management methods
SawfishIntegration::generateToken();
SawfishIntegration::refreshToken();
SawfishIntegration::revokeToken();

// Accounts methods
SawfishIntegration::getAccounts();

// Client methods
SawfishIntegration::getClients();
SawfishIntegration::createClient();
SawfishIntegration::getClientByUuids();
SawfishIntegration::findClients($name, $email); // name OR email
SawfishIntegration::addContactPersons();
SawfishIntegration::updateContactPersons();

// Invoice methods
SawfishIntegration::getInvoices();
SawfishIntegration::createInvoice();
SawfishIntegration::updateInvoice();
SawfishIntegration::voidInvoice();
SawfishIntegration::sendInvoice();
SawfishIntegration::getPdfInvoiceLink();
SawfishIntegration::addInvoiceAttachments();
SawfishIntegration::deleteInvoiceAttachments();
SawfishIntegration::manualInvoicePayment();

// Items methods
SawfishIntegration::getItems();
```

