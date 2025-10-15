<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

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

        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
    }

    public function boot(): void
    {
        $this->registerCommands();

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
}
