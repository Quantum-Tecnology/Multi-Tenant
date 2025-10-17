<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Models\Concerns;

trait CentraConnection
{
    public function getConnectionName(): string
    {
        return env('DB_CONNECTION');
    }
}
