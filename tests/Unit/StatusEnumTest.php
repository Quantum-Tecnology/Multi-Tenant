<?php

use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;

it('has expected enum values', function () {
    expect(StatusEnum::SUCCESS->value)->toBe(1)
        ->and(StatusEnum::PENDING->value)->toBe(2)
        ->and(StatusEnum::ERROR->value)->toBe(3)
        ->and(StatusEnum::ERROR_ON_ROLLBACK->value)->toBe(4);
});
