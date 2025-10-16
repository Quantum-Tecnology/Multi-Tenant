<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support\UniqueIdentifierGenerators;

use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\Tenant\Contracts\UniqueIdentifierInterface;
use Ramsey\Uuid\Uuid;

final class UUIDGenerator implements UniqueIdentifierInterface
{
    public static function generate(Model $model): string
    {
        return Uuid::uuid7()->toString();
    }
}
