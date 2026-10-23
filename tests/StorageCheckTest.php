<?php

declare(strict_types=1);

use IllumaLaw\HealthCheckStorage\StorageDiskWritabilityCheck;
use Illuminate\Support\Facades\Storage;
use Spatie\Health\Enums\Status;

it('succeeds when configured disks are writable', function () {
    config()->set('filesystems.disks.local', ['driver' => 'local']);

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('put')->once()->andReturn(true);
    $mockDisk->shouldReceive('get')->once()->andReturn('ok');
    $mockDisk->shouldReceive('delete')->once()->andReturn(true);

    Storage::shouldReceive('disk')->with('local')->once()->andReturn($mockDisk);

    $result = StorageDiskWritabilityCheck::new()
        ->disks(['local'])
        ->run();

    expect($result->status)->toEqual(Status::ok())
        ->and($result->shortSummary)->toBe('1 disk(s)');
});

it('fails when a disk is not writable', function () {
    config()->set('filesystems.disks.local', ['driver' => 'local']);

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('put')->once()->andThrow(new Exception('Disk full'));

    Storage::shouldReceive('disk')->with('local')->once()->andReturn($mockDisk);

    $result = StorageDiskWritabilityCheck::new()
        ->disks(['local'])
        ->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('local: Disk full');
});

it('deletes the probe file when a read mismatch occurs', function () {
    config()->set('filesystems.disks.local', ['driver' => 'local']);

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('put')->once()->andReturn(true);
    $mockDisk->shouldReceive('get')->once()->andReturn('unexpected');
    $mockDisk->shouldReceive('delete')->once()->andReturn(true);

    Storage::shouldReceive('disk')->with('local')->once()->andReturn($mockDisk);

    $result = StorageDiskWritabilityCheck::new()
        ->disks(['local'])
        ->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('local: read mismatch');
});

it('deletes the probe file when reading throws', function () {
    config()->set('filesystems.disks.local', ['driver' => 'local']);

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('put')->once()->andReturn(true);
    $mockDisk->shouldReceive('get')->once()->andThrow(new Exception('Read failed'));
    $mockDisk->shouldReceive('delete')->once()->andReturn(true);

    Storage::shouldReceive('disk')->with('local')->once()->andReturn($mockDisk);

    $result = StorageDiskWritabilityCheck::new()
        ->disks(['local'])
        ->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('local: Read failed');
});

it('fails when the probe file cannot be deleted', function () {
    config()->set('filesystems.disks.local', ['driver' => 'local']);

    $mockDisk = Mockery::mock();
    $mockDisk->shouldReceive('put')->once()->andReturn(true);
    $mockDisk->shouldReceive('get')->once()->andReturn('ok');
    $mockDisk->shouldReceive('delete')->once()->andReturn(false);

    Storage::shouldReceive('disk')->with('local')->once()->andReturn($mockDisk);

    $result = StorageDiskWritabilityCheck::new()
        ->disks(['local'])
        ->run();

    expect($result->status)->toEqual(Status::failed())
        ->and($result->notificationMessage)->toContain('local: probe file could not be deleted');
});

it('skips when no valid disks are provided', function () {
    $result = StorageDiskWritabilityCheck::new()
        ->disks([])
        ->run();

    expect($result->status)->toEqual(Status::skipped());
});
