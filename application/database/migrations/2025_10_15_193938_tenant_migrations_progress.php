<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('tenant.table.progress'), function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('batch_id');
            $table->unsignedTinyInteger('status')->nullable();
            $table->text('data')->nullable();
            $table->string('last_batch');
            $table->timestamps();
            $table->index(['batch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tenant.table.progress'));
    }
};
