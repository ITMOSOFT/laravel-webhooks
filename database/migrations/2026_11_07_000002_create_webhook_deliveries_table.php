<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');

        Schema::create($prefix.'webhook_deliveries', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('endpoint_id')->constrained($prefix.'webhook_endpoints')->cascadeOnDelete();
            $table->uuid('event_id')->index();
            $table->string('event_type')->index();
            $table->json('payload');
            $table->string('status', 24)->default('pending')->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');
        Schema::dropIfExists($prefix.'webhook_deliveries');
    }
};
