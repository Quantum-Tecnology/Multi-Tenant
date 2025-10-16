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
        if (tenant()->id === '01k7m0ngt49hscpqar742qqkaa') {
            //            throw new Exception('oi tudo bem');
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
