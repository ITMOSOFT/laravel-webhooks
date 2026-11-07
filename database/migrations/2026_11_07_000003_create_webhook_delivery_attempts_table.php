<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');

        Schema::create($prefix.'webhook_delivery_attempts', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('delivery_id')->constrained($prefix.'webhook_deliveries')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('response_excerpt')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at');
            $table->unique(['delivery_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');
        Schema::dropIfExists($prefix.'webhook_delivery_attempts');
    }
};
