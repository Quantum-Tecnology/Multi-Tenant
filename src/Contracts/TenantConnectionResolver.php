<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Contracts;

use QuantumTecnology\Tenant\Models\Tenant;

/**
 * Contract that defines how to resolve a tenant database connection configuration.
 *
 * Implement this interface to fully customize how the application connects to a tenant.
 */
interface TenantConnectionResolver
{
    /**
     * Builds the connection configuration array for the given tenant, based on the base/default
     * connection configuration provided by the application.
     *
     * @param  array<string, mixed>  $baseConnection  The base/default connection config (e.g., from config('database.connections.mysql'))
     * @return array<string, mixed>
     */
    public function buildConnectionConfig(Tenant $tenant, array $baseConnection, array $dataTenant): array;

    /**
     * The name of the connection that should be registered for tenants.
     *
     * Returning a fixed name keeps things simple (e.g. "tenant"). You can also implement logic
     * to return different names per tenant when necessary.
     */
    public function connectionName(Tenant $tenant): string;
}
