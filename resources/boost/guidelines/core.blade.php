# eloquage/healthcheck-storage

Checks if the `vector` extension (storage) is enabled and active in PostgreSQL.

## Usage

```php
use IllumaLaw\HealthCheckStorage\StorageExtensionCheck;
use Spatie\Health\Facades\Health;

Health::checks([
    StorageExtensionCheck::new()
        ->required(true), // If true, FAIL if missing. If false, WARNING.
]);
```

## Configuration

Publish config: `php artisan vendor:publish --tag="healthcheck-storage-config"`

Options in `config/healthcheck-storage.php`:
- `required`: (bool) Global default for strictness.
