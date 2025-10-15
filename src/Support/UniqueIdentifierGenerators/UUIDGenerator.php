<?php

namespace QuantumTecnology\Tenant\Support\UniqueIdentifierGenerators;

use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\Tenant\Contracts\UniqueIdentifierInterface;
use Ramsey\Uuid\Uuid;

class UUIDGenerator implements UniqueIdentifierInterface
{
    public static function generate(Model $model): string|int
    {
        return Uuid::uuid7()->toString();
    }
}