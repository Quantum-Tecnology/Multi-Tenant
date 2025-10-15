<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'Default Tenant',
            'domain' => 'localhost',
            'database' => 'multitenant_demo'
        ]);

        Tenant::create([
            'name' => 'Second Tenant',
            'domain' => '127.0.0.1',
            'database' => 'multitenant_demo2'
        ]);

        Tenant::create([
            'name' => 'Third Tenant',
            'domain' => 'testing',
            'database' => 'multitenant_demo3'
        ]);
    }
}
