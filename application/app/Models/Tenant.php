<?php

namespace App\Models;

class Tenant extends \QuantumTecnology\Tenant\Models\Tenant
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
