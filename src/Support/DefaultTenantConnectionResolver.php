<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use QuantumTecnology\Tenant\Contracts\TenantConnectionResolver;
use QuantumTecnology\Tenant\Models\Tenant;

/**
 * Default implementation: merges tenant->data over the base connection and uses a fixed
 * connection name 'tenant'.
 */
final class DefaultTenantConnectionResolver implements TenantConnectionResolver
{
    /**
     * @param  array<string, mixed>  $baseConnection
     * @return array<string, mixed>
     */
    public function buildConnectionConfig(Tenant $tenant, array $baseConnection): array
    {
        return array_merge($baseConnection, $tenant->data ?? []);
    }

    public function connectionName(Tenant $tenant): string
    {
        return 'tenant';
    }
}
