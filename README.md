# Laravel Multi-Tenant (QuantumTecnology\Tenant)

A small, flexible Laravel package to manage multi-tenant applications. It lets you switch the application context to a specific tenant, connect to its database using pluggable strategies, and optionally apply environment side-effects (like cache prefixes). The package also provides a convenient command to run tenant migrations per tenant, either synchronously or via queued batch jobs with automatic rollback on failures.

- Framework: Laravel 10–12 components
- PHP: 8.2+

## Features
- Pluggable connection strategy via TenantConnectionResolver contract
- Pluggable environment adjuster via TenantEnvironmentApplier contract
- Simple Tenant model with optional custom ID generator
- Helper to access the current tenant in the container
- Queue-aware tenant propagation for Jobs
- Per-tenant migrations command (sync or queued batch)
- Automatic rollback of already migrated tenants if a batch fails

## Installation

Install via Composer:

```bash
composer require quantumtecnology/multi-tenant
```

The service provider is auto-discovered:

- QuantumTecnology\Tenant\TenantServiceProvider

## Publishing

Publish the configuration file:

```bash
php artisan vendor:publish --tag=tenant-config
```

This will create config/tenant.php in your application.

Publish the baseline migrations provided by the package:

```bash
php artisan vendor:publish --tag=tenant-migrations
```

This will publish:
- A tenants table migration
- A tenant_migrations_progress table migration (used to track migration batches/status)

## Configuration (config/tenant.php)

Key options you can tweak:

- model.tenant: Your Tenant Eloquent model class. Defaults to QuantumTecnology\Tenant\Models\Tenant.
- model.id_generator: Class that implements UniqueIdentifierInterface to generate IDs (e.g. UUIDs) for new tenants.
- database.migrations: Directory containing your tenant migrations (e.g. database/migrations/tenant).
- database.seeder: Seeder class to run after tenant migrations when requested.
- table.progress: Table used to store migration progress and status (default: tenant_migrations_progress).
- queue.name: Queue name used by the package jobs (default: default).

Note: Ensure your database.default is a central connection. The package dynamically registers a separate connection for the active tenant when switching context.

## How it works

- TenantManager is responsible for switching to a tenant and reconnecting the database using a connection name determined by the resolver (defaults to tenant). It also delegates environment updates (e.g., cache prefixes) to the environment applier and stores the current tenant in the container as tenant.
- DefaultTenantConnectionResolver merges the tenant data over your base/default DB connection config to create the tenant connection config.
- DefaultTenantEnvironmentApplier sets cache/redis prefixes per tenant and binds the current tenant instance into the container.

### Accessing the current tenant

Use the helper:

```php
$tenant = tenant(); // returns the current tenant bound in the container, or null
```

Or resolve directly from the container:

```php
$tenant = app('tenant');
```

## Running tenant migrations

Command:

```bash
php artisan tenants:migrate [--tenant_id=] [--fresh] [--seed]
```

- --tenant_id: Migrate only the specified tenant ID; when omitted, all tenants are processed.
- --fresh: Uses migrate:fresh instead of migrate.
- --seed: Runs the configured tenant database seeder class after migrating.

Execution mode:
- If your queue.default is sync, the command runs synchronously for each tenant.
- Otherwise, it dispatches a batch of jobs (MigrateTenantJob) to the queue defined by tenant.queue.name.

Batch behavior:
- Each tenant migration is tracked in table tenant_migrations_progress with a batch_id (the batch UUID).
- If any tenant migration fails in a batch, the package dispatches a RollbackBatchJob to rollback previously successful tenants to their last known batch step.

## Extensibility

You can fully customize how tenant connections are established and how environment changes are applied by binding your own implementations to these contracts:

- QuantumTecnology\Tenant\Contracts\TenantConnectionResolver
- QuantumTecnology\Tenant\Contracts\TenantEnvironmentApplier

Example: Custom connection resolver

```php
use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;
use QuantumTecnology\Tenant\Models\Tenant;

class MyResolver implements TenantConnectionResolver
{
    public function buildConnectionConfig(Tenant $tenant, array $base, array $dataTenant): array
    {
        return array_merge($base, [
            'database' => $tenant->database,
            'username' => $tenant->db_user,
            'password' => $tenant->db_pass,
        ]);
    }

    public function connectionName(Tenant $tenant): string
    {
        return 'tenant';
    }
}
```

Register your resolver (e.g. in a service provider registered after the package provider):

```php
use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;

$this->app->singleton(TenantConnectionResolver::class, MyResolver::class);
```

Similarly, you can replace the environment applier to add custom behavior during switch and reset.

## Tenant model

The default model QuantumTecnology\Tenant\Models\Tenant:
- Uses a primary key id
- Supports optional custom ID generation via model.id_generator (e.g., UUIDs)
- Provides getTenantKeyName() and getTenantKey() helpers

You can swap this model via config('tenant.model.tenant') with your own App\Models\Tenant.

## Queue propagation

The package attaches the current tenant_id to the queue payload when jobs are dispatched. Before processing a job, the package will re-apply the tenant context automatically so your jobs run within the correct tenant connection and environment.

## Local testing & development

- Set up a central database connection in config/database.php.
- Create the tenants table and seed your tenant records.
- Set config('tenant.database.migrations') to your tenant migrations directory and create the tenant migrations there.
- Run php artisan tenants:migrate to migrate tenants either synchronously (sync driver) or via queue.

## Version compatibility

- Laravel components 10–12
- PHP 8.2+

## License

MIT License. See LICENSE file if present. © Contributors.

## Credits

- Author: Bruno Costa (bhcosta90@gmail.com)
- Package namespace: QuantumTecnology\\Tenant