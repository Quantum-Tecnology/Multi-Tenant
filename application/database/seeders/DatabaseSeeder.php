<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::create([
            'name' => 'Default Tenant',
            'domain' => 'localhost',
            'database' => 'wms_demo_1',
        ]);

        //        Tenant::create([
        //            'id' => '0199edf5-524b-70fa-9dc7-69950f5664cf',
        //            'name' => 'Second Tenant',
        //            'domain' => '127.0.0.1',
        //            'database' => 'multitenant_demo2',
        //        ]);

        //        Tenant::create([
        //            'id' => '0199edf5-5256-724d-8147-68a993ad2e9d',
        //            'name' => 'Third Tenant',
        //            'domain' => 'testing',
        //            'database' => 'multitenant_demo3',
        //        ]);
    }
}
