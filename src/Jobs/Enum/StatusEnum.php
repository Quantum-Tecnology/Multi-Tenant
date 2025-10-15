<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs\Enum;

enum StatusEnum: int
{
    case SUCCESS = 1;
    case PENDING = 2;
    case ERROR = 3;
    case ERROR_ON_ROLLBACK = 4;
}
