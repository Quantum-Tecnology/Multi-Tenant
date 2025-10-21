<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class TenantSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'email' => 'test@example.com'
        ]);
    }
}
