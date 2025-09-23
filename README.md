# This is a package for Sawfish accounting API integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sprint-digital/sawfish-integration.svg?style=flat-square)](https://packagist.org/packages/sprint-digital/sawfish-integration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sprint-digital/sawfish-integration/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sprint-digital/sawfish-integration/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/sprint-digital/sawfish-integration/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/sprint-digital/sawfish-integration/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sprint-digital/sawfish-integration.svg?style=flat-square)](https://packagist.org/packages/sprint-digital/sawfish-integration)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/sawfish-integration.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/sawfish-integration)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require sprint-digital/sawfish-integration
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="sawfish-integration-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sawfish-integration-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Testing

```bash
composer test
```

This is to setup sawfish integration client id and api key

```bash
php artisan sawfish:integrate"
```

## Usage

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->generateToken();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->revokeToken();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->refreshToken();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->getAccounts();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->getClients();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->createClient();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->getClientByUuids();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->addContactPersons();
```

```php
$sawfishIntegration = new SprintDigital\SawfishIntegration();
echo $sawfishIntegration->updateContactPersons();
```

