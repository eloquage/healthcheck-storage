<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckStorage\HealthcheckStorageServiceProvider;
use Illuminate\Support\ServiceProvider;

it('registers the config publish path under the correct tag', function () {
    $provider = app()->getProvider(IllumaLaw\HealthCheckStorage\HealthcheckStorageServiceProvider::class);
    $source = dirname((new \ReflectionClass($provider))->getFileName()).'/../config/healthcheck-storage.php';

    expect(ServiceProvider::pathsToPublish($provider::class, 'healthcheck-storage-config'))
        ->toBe([$source => config_path('healthcheck-storage.php')]);
});

it('loads config with the correct default', function () {
    expect(config('healthcheck-storage.disks'))->toBeArray();
});
