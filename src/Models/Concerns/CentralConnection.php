<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Models\Concerns;

trait CentralConnection
{
    public function getConnectionName(): string
    {
        return 'central';
    }
}
