# Laravel Passport client scopes restriction

[![Latest Version on Packagist](https://img.shields.io/packagist/v/denniseilander/laravel-passport-scopes-restriction.svg?style=flat-square)](https://packagist.org/packages/denniseilander/laravel-passport-scopes-restriction)
[![Total Downloads](https://img.shields.io/packagist/dt/denniseilander/laravel-passport-scopes-restriction.svg?style=flat-square)](https://packagist.org/packages/denniseilander/laravel-passport-scopes-restriction)

This package allows you to limit the scopes a client can request.<br>
By default, [Laravel Passport](https://laravel.com/docs/master/passport) doesn't support restricting scopes per client.
Every client can access all available scopes in your project. This package solves that problem.

## When to use this package
When your api project contains multiple third party oauth_clients, and you can't control which scopes they request,
you may want to limit the scopes a client can request.

## Installation
You can install the package via composer:
```bash
composer require denniseilander/laravel-passport-scopes-restriction
```

You can publish and run the migrations with:
```bash
php artisan vendor:publish --provider="Denniseilander\PassportScopeRestriction\PassportClientServiceProvider" --tag="passport-scopes-restriction-migrations"
php artisan migrate
```

Optionally you can publish the config file with:
```bash
php artisan vendor:publish --provider="Denniseilander\PassportScopeRestriction\PassportClientServiceProvider" --tag="passport-scopes-restriction-config"
```

## Usage
After running the migration, you may add specific scopes to each of your oauth_clients `allowed_scopes` column.
You can assign specific scopes the same way as they are assigned to the oauth_access_tokens `scopes` column:
```php
// one scope
["read-users"]

// multiple scopes
["read-users","edit-users"]
```
Every time an access token is requested for a specific client, the `allowed_scopes` will be added to the `scopes` column of that token.

## Syncing existing scopes with new allowed scopes
Sometimes you have your `oauth_access_tokens` table filled with existing tokens and want to update the scopes
because you've changed the `allowed_scopes` value of a specific client.<br><br>
This package makes it easy to synchronize exiting token scopes with your allowed scopes by running the following command:
```bash
php artisan passport:scopes-sync
```

If you've **added new scopes** to the `allowed_scopes` column on the clients table,
but you want to keep the existing scopes on your tokens, you may add the `--keep-existing-scopes` flag to the sync command:
```bash
php artisan passport:scopes-sync --keep-existing-scopes
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
- [Dennis Eilander](https://github.com/denniseilander)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
