# Healthcheck Storage for Laravel

![Healthcheck Storage banner](art/banner.png)

A [Spatie Laravel Health](https://spatie.be/docs/laravel-health) check that
writes, reads, and deletes a small probe file on each configured Laravel
disk.

[![Tests](https://github.com/eloquage/healthcheck-storage/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/healthcheck-storage/actions/workflows/run-tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/illuma-law/healthcheck-storage)](https://packagist.org/packages/illuma-law/healthcheck-storage)
[![License](https://img.shields.io/packagist/l/illuma-law/healthcheck-storage)](LICENSE.md)

## Requirements

- PHP 8.3 or later
- Laravel 12 or 13
- Spatie Laravel Health 1.39 or later

## Install

```bash
composer require illuma-law/healthcheck-storage
```

Register the check with your other health checks:

```php
use IllumaLaw\HealthCheckStorage\StorageDiskWritabilityCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    StorageDiskWritabilityCheck::new()->disks(['local', 'public']),
]);
```

By default, the check probes the `local` and `public` disks. Set
`HEALTH_STORAGE_DISKS=s3,archive` or publish the package config to choose
other disks:

```bash
php artisan vendor:publish --tag=healthcheck-storage-config
```

Each probe writes a small file, reads it back, and deletes it. A write, read,
or cleanup error fails the check. Unknown disk names are skipped; if no
configured disks can be probed, the result is skipped.

## Test

```bash
composer test
composer analyse
vendor/bin/pint --test
```

MIT licensed. See [LICENSE.md](LICENSE.md).
