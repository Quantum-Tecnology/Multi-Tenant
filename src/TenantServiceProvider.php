<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;
use QuantumTecnology\Tenant\Contracts\TenantEnvironmentApplier;
use QuantumTecnology\Tenant\Contracts\UniqueIdentifierInterface;
use QuantumTecnology\Tenant\Support\DefaultTenantConnectionResolver;
use QuantumTecnology\Tenant\Support\DefaultTenantEnvironmentApplier;
use QuantumTecnology\Tenant\Support\TenantManager;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singletonIf(TenantConnectionResolver::class, DefaultTenantConnectionResolver::class);
        $this->app->singletonIf(TenantEnvironmentApplier::class, DefaultTenantEnvironmentApplier::class);

        // Allow users to override the resolver binding in a higher-priority provider if needed
        $this->app->singletonIf(TenantManager::class, fn ($app): TenantManager => new TenantManager(
            app(TenantConnectionResolver::class),
            app(TenantEnvironmentApplier::class)
        ));

        $this->mergeConfigFrom(
            __DIR__.'/../config/tenant.php',
            'tenant'
        );

        $this->configurePublishers();

        $this->configureGenerateId();

        $this->registerCommands();
    }

    public function boot(): void
    {
        // Injetar tenant_id no payload
        Queue::createPayloadUsing(function ($connection, $queue, array $payload): array {
            if (tenant()) {
                $payload['tenant_id'] = tenant()->id;
            }

            return $payload;
        });

        // Antes de processar o job
        Queue::before(function ($event): void {
            $payload = $event->job->payload();

            if (isset($payload['tenant_id'])) {
                $model = config('tenant.model.tenant');
                app(TenantManager::class)->disconnect();
                $tenant = $model::query()->find($payload['tenant_id']);
                if ($tenant) {
                    app(TenantManager::class)->switchTo($tenant);
                }
            }
        });
    }

    private function configurePublishers(): void
    {
        $this->publishes([
            __DIR__.'/../config/tenant.php' => config_path('tenant.php'),
        ], 'tenant-config');

        $this->publishes([
            __DIR__.'/Migrations/0000_00_00_000000_create_tenants_table.php' => $this->getMigrationFileName('0000_00_00_000000_create_tenants_table.php', false),
            __DIR__.'/Migrations/0000_00_00_000000_tenant_migrations_progress.php' => $this->getMigrationFileName('tenant_migrations_progress.php'),
        ], 'tenant-migrations');
    }

    private function configureGenerateId(): void
    {
        if (! is_null(config('tenant.model.id_generator'))) {
            $this->app->singletonIf(UniqueIdentifierInterface::class, config('tenant.model.id_generator'));
        }
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\MigrateCommand::class,
        ]);
    }

    private function getMigrationFileName(string $migrationFileName, bool $timestamp = true): string
    {
        $timestamp = when($timestamp, fn (): string => date('Y_m_d_His').'_');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}{$migrationFileName}")
            ->first();
    }
}
