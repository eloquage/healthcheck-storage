# Healthcheck storage for Laravel

[![Tests](https://github.com/eloquage/healthcheck-storage/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-storage/actions)
[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-storage?label=Version)](https://packagist.org/packages/illuma-law/healthcheck-storage)

A focused storage health check for Spatie's [Laravel Health](https://spatie.be/docs/laravel-health/v1/introduction) package.

This package provides a direct health check to verify that your configured storage disks (local, S3, etc.) are actually writable by the application.

## Features

- **Write-Read-Delete Cycle:** Performs a complete cycle (write small file, read it back, delete it) to ensure full disk operationality.
- **Multi-Disk Support:** Monitor multiple disks at once (defaults to `local` and `public`).
- **Performance Tracking:** Reports the latency of the storage operations for each disk in the health meta data.

## Installation

Require this package with composer:

```shell
composer require illuma-law/healthcheck-storage
```

## Usage & Integration

Register the check inside your application's health service provider (e.g. `AppServiceProvider` or a dedicated `HealthServiceProvider`), alongside your other Spatie Laravel Health checks:

### Basic Registration

```php
use IllumaLaw\HealthCheckStorage\StorageDiskWritabilityCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    StorageDiskWritabilityCheck::new()
        ->disks(['local', 'public', 's3']),
]);
```

### Expected Result States

The check interacts with the Spatie Health dashboard and JSON endpoints using these states:

- **Ok:** All probed disks successfully completed the write-read-delete cycle.
- **Skipped:** No valid disks were configured or found to probe.
- **Failed:** One or more disks failed to write, read back, or delete the probe file.

## Testing

Run the test suite:

```shell
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
