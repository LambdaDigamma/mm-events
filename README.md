# Handle events for Mein Moers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lambdadigamma/mm-events.svg?style=flat-square)](https://packagist.org/packages/lambdadigamma/mm-events)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/lambdadigamma/mm-events/run-tests?label=tests)](https://github.com/lambdadigamma/mm-events/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/lambdadigamma/mm-events.svg?style=flat-square)](https://packagist.org/packages/lambdadigamma/mm-events)

A package providing events for the Mein Moers platform.

## Installation

You can install the package via composer:

```bash
composer require lambdadigamma/mm-events
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="LambdaDigamma\MMEvents\MMEventsServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="LambdaDigamma\MMEvents\MMEventsServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Lennart Fischer](https://github.com/LambdaDigamma)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
