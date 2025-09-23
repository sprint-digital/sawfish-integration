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

This is to setup sawfish integration client id and api key

```bash
php artisan sawfish:integrate
```

## Usage

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
$sawfishIntegration->generateToken();
$sawfishIntegration->refreshToken();
$sawfishIntegration->revokeToken();
$sawfishIntegration->getAccounts();
$sawfishIntegration->createClient();
$sawfishIntegration->getClients();
$sawfishIntegration->getClientByUuids();
$sawfishIntegration->addContactPersons();
$sawfishIntegration->updateContactPersons();
```

