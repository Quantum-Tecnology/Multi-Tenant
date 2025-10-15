<?php

namespace QuantumTecnology\Tenant\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UniqueIdentifierInterface
{
    /**
     * Generate a unique identifier for a model.
     */
    public static function generate(Model $model): string|int;
}