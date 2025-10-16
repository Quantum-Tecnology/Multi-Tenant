<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;
use QuantumTecnology\Tenant\Contracts\TenantEnvironmentApplier;
use QuantumTecnology\Tenant\Models\Tenant;

final class TenantManager
{
    private ?Tenant $tenant = null;

    private readonly string $originalDefault;

    public function __construct(
        public ?TenantConnectionResolver $resolver = null,
        public ?TenantEnvironmentApplier $environment = null
    ) {
        $this->originalDefault = config('database.default');
        $this->resolver ??= new DefaultTenantConnectionResolver();
        $this->environment ??= new DefaultTenantEnvironmentApplier();
    }

    /**
     * Conecta ao tenant e ajusta cache
     */
    public function switchTo(Tenant $tenant): void
    {
        $this->tenant = $tenant;

        $base = config('database.connections.'.$this->originalDefault);

        $connectionConfig = $this->resolver->buildConnectionConfig($tenant, $base, $tenant->data ?? []);
        $connectionName = $this->resolver->connectionName($tenant);

        Config::set('database.connections.'.$connectionName, $connectionConfig);
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        // We maintain central default
        Config::set('database.default', $connectionName);

        // Delegate environment side-effects (cache, container binding, etc.)
        $this->environment->apply($tenant, $connectionName);
    }

    /**
     * Disconnects from the tenant and returns to default
     */
    public function disconnect(): void
    {
        $this->tenant = null;
        Config::set('database.default', $this->originalDefault);

        // Delegate reset of environment
        $this->environment->reset();
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }
}
