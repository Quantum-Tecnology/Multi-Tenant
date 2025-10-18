<?php

declare(strict_types=1);

namespace App\Models;

final class Tenant extends \QuantumTecnology\Tenant\Models\Tenant
{
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'domain',
        ];
    }
}
