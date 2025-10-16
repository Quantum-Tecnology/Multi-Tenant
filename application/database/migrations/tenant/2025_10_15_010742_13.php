<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (tenant()->id === '01k7m0ngtct1f7k3zdw8p2t66s') {
            //            throw new Exception('oi tudo bem 2');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
