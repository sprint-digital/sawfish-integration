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

## Testing

```bash
composer test
```

## Setup

This is to setup sawfish integration, this creates the table and asks for the sawfish credentials

```bash
php artisan sawfish:integrate
```

## Usage

```php
$sawfishIntegration = SprintDigital\SawfishIntegration();

// Token management methods
$sawfishIntegration->generateToken();
$sawfishIntegration->refreshToken();
$sawfishIntegration->revokeToken();

// Accounts methods
$sawfishIntegration->getAccounts();

// Client methods
$sawfishIntegration->getClients();
$sawfishIntegration->createClient();
$sawfishIntegration->getClientByUuids();
$sawfishIntegration->addContactPersons();
$sawfishIntegration->updateContactPersons();

// Invoice methods
$sawfishIntegration->getInvoices();
$sawfishIntegration->createInvoice();
$sawfishIntegration->updateInvoice();
$sawfishIntegration->voidInvoice();
$sawfishIntegration->sendInvoice();
$sawfishIntegration->getPdfInvoiceLink();
$sawfishIntegration->addInvoiceAttachments();
$sawfishIntegration->deleteInvoiceAttachments();
$sawfishIntegration->manualInvoicePayment();

// Items methods
$sawfishIntegration->getItems();
```

