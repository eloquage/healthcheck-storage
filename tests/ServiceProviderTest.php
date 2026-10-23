<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('publishes the config file under the correct tag', function () {
    $command = $this->artisan('vendor:publish', [
        '--tag' => 'healthcheck-storage-config',
        '--force' => true,
    ]);

    if ($command instanceof \Illuminate\Testing\PendingCommand) {
        $command->assertExitCode(0);
    } else {
        expect($command)->toBe(0);
    }

    expect(config_path('healthcheck-storage.php'))->toBeFile();

    File::delete(config_path('healthcheck-storage.php'));
});

it('loads config with the correct default', function () {
    expect(config('healthcheck-storage.disks'))->toBeArray();
});
