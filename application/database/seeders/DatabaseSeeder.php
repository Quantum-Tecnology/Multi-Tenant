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
        Tenant::factory()->create([
            'domain' => '127.0.0.1',
            'data' => [
                'database' => 'multitenant_demo'
            ],
        ]);

        Tenant::factory()->create([
            'id' => '01k7m0ngt49hscpqar742qqkaa',
            'domain' => 'localhost',
            'data' => [
                'database' => 'multitenant_demo2'
            ],
        ]);

        Tenant::factory()->create([
            'id' => '01k7m0ngtct1f7k3zdw8p2t66s',
            'domain' => 'testing',
            'data' => [
                'database' => 'multitenant_demo3'
            ],
        ]);
    }
}
