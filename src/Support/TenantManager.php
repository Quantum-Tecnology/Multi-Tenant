<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;
use QuantumTecnology\Tenant\Contracts\TenantEnvironmentResolver;
use QuantumTecnology\Tenant\Contracts\TenantQueueResolver;
use QuantumTecnology\Tenant\Models\Tenant;

final class TenantManager
{
    private ?Tenant $tenant = null;

    private readonly string $originalDefault;

    public function __construct(
        public ?TenantConnectionResolver $resolver = null,
        public ?TenantQueueResolver $queue = null,
        public ?TenantEnvironmentResolver $environment = null
    ) {
        $this->originalDefault = config('database.default');

        if (blank($this->resolver)) {
            $this->resolver = app(TenantConnectionResolver::class);
        }

        if (blank($this->environment)) {
            $this->environment = app(TenantEnvironmentResolver::class);
        }

        if (blank($this->queue)) {
            $this->queue = app(TenantQueueApply::class);
        }
    }

    /**
     * Conecta ao tenant e ajusta cache
     */
    public function switchTo(Tenant $tenant): self
    {
        $this->tenant = $tenant;

        $base = config('database.connections.'.$this->originalDefault);

        $connectionConfig = $this->resolver->buildConnectionConfig($tenant, $base, $tenant->getOriginal('data') ?? []);
        $connectionName = $this->resolver->connectionName($tenant);

        Config::set('database.connections.'.$connectionName, $connectionConfig);
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        // We maintain central default
        if (blank(config('database.connections.default'))) {
            Config::set('database.connections.default', $connectionName);
        }

        Config::set('database.default', $connectionName);

        // Delegate environment side-effects (cache, container binding, etc.)
        $this->environment->apply($tenant, $connectionName);
        $this->queue->buildConnectionConfig('central');

        return $this;
    }

    /**
     * Disconnects from the tenant and returns to default
     */
    public function disconnect(): self
    {
        $this->tenant = null;
        Config::set('database.default', $this->originalDefault);

        // Delegate reset of environment
        $this->environment->reset();

        return $this;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }
}
