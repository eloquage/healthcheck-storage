<?php

declare(strict_types=1);

namespace IllumaLaw\HealthCheckStorage;

use Illuminate\Support\Facades\Storage;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Throwable;

final class StorageDiskWritabilityCheck extends Check
{
    /** @var array<string>|null */
    protected ?array $disks = null;

    /**
     * @param  array<string>  $disks
     */
    public function disks(array $disks): self
    {
        $this->disks = $disks;

        return $this;
    }

    public function run(): Result
    {
        $disksToMonitor = $this->disks ?? (array) config('healthcheck-storage.disks', ['local', 'public']);
        $skippedUnknown = [];
        $latencies = [];
        $errors = [];

        foreach ($disksToMonitor as $diskName) {
            if (! is_string($diskName)) {
                continue;
            }

            $diskName = trim($diskName);

            if ($diskName === '') {
                continue;
            }

            if (config("filesystems.disks.{$diskName}") === null) {
                $skippedUnknown[] = $diskName;

                continue;
            }

            $path = 'health/.probe-'.uniqid('', true);
            $payload = 'ok';
            $disk = null;
            $probeSucceeded = false;
            $cleanupSucceeded = false;
            $started = microtime(true);

            try {
                $disk = Storage::disk($diskName);

                if (! $disk->put($path, $payload)) {
                    $errors[] = "{$diskName}: write failed";
                } elseif ($disk->get($path) !== $payload) {
                    $errors[] = "{$diskName}: read mismatch";
                } else {
                    $probeSucceeded = true;
                }
            } catch (Throwable $e) {
                $errors[] = "{$diskName}: ".$e->getMessage();
            } finally {
                if ($disk !== null) {
                    try {
                        if (! $disk->delete($path)) {
                            $errors[] = "{$diskName}: probe file could not be deleted";
                        } else {
                            $cleanupSucceeded = true;
                        }
                    } catch (Throwable $e) {
                        $errors[] = "{$diskName}: probe cleanup failed: ".$e->getMessage();
                    }
                }
            }

            if ($probeSucceeded && $cleanupSucceeded) {
                $latencies[$diskName] = round((microtime(true) - $started) * 1000, 2);
            }
        }

        $result = Result::make()->meta([
            'write_read_delete_ms' => $latencies,
            'skipped_unknown_disks' => $skippedUnknown,
        ]);

        if ($errors !== []) {
            return $result->failed('Disk writability issues: '.implode('; ', $errors));
        }

        if ($latencies === []) {
            return (new Result(Status::skipped(), 'No configured disks were available to probe.'))
                ->meta([
                    'write_read_delete_ms' => [],
                    'skipped_unknown_disks' => $skippedUnknown,
                ])
                ->shortSummary('Skipped');
        }

        return $result->ok('Configured disks are writable.')->shortSummary(count($latencies).' disk(s)');
    }
}
