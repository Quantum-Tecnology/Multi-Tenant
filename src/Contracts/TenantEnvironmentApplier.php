<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Contracts;

use QuantumTecnology\Tenant\Models\Tenant;

/**
 * Abstraction to apply and reset environment side-effects when switching tenant context.
 */
interface TenantEnvironmentApplier
{
    /**
     * Apply environment changes after switching to the given tenant.
     */
    public function apply(Tenant $tenant, string $connectionName): void;

    /**
     * Reset environment changes when disconnecting from a tenant.
     */
    public function reset(): void;
}
