<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Contracts;

interface TenantQueueResolver
{
    public function buildConnectionConfig(string $connect): void;
}
