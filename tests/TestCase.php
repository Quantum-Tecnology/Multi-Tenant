<?php

namespace Tests;

use QuantumTecnology\Tenant\TenantServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TenantServiceProvider::class,
        ];
    }
}
