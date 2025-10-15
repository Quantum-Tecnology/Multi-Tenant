<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Allow users to override the resolver binding in a higher-priority provider if needed
        $this->app->singletonIf(TenantManager::class, fn ($app): TenantManager => new TenantManager());

        $this->mergeConfigFrom(
            __DIR__.'/../config/tenant.php',
            'tenant'
        );

        $this->publishes([
            __DIR__.'/../../config/tenant.php' => config_path('tenant.php'),
        ], 'tenant-config');

        $this->publishes([
            __DIR__.'/Migrations/0000_00_00_000000_create_tenants_table.php' => $this->getMigrationFileName('0000_00_00_000000_create_tenants_table.php', false),
            __DIR__.'/Migrations/0000_00_00_000000_tenant_migrations_progress.php' => $this->getMigrationFileName('tenant_migrations_progress.php'),
        ], 'tenant-migrations');
    }

    public function boot(): void
    {
        // Injetar tenant_id no payload
        Queue::createPayloadUsing(function ($connection, $queue, array $payload): array {
            $tenant = app(TenantManager::class)->getTenant();
            if ($tenant) {
                $payload['tenant_id'] = $tenant->id;
            }

            return $payload;
        });

        // Antes de processar o job
        Queue::before(function ($event): void {
            $payload = $event->job->payload();

            if (isset($payload['tenant_id'])) {
                $tenant = Tenant::query()->find($payload['tenant_id']);
                if ($tenant) {
                    app(TenantManager::class)->switchTo($tenant);
                }
            }
        });
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

    protected function getMigrationFileName(string $migrationFileName, $timestamp = true): string
    {
        $timestamp = when($timestamp, fn() => date('Y_m_d_His') . '_');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}{$migrationFileName}")
            ->first();
    }
}
