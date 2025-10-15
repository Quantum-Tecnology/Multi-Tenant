<?php

use QuantumTecnology\Tenant\Models\Tenant;

it('casts data to array and allows mass assignment of fillables', function () {
    $tenant = new Tenant([
        'name' => 'ACME',
        'data' => ['driver' => 'sqlite'],
    ]);

    expect($tenant->getAttribute('name'))->toBe('ACME')
        ->and($tenant->getAttribute('data'))
        ->toBeArray()
        ->toHaveKey('driver', 'sqlite');
});
