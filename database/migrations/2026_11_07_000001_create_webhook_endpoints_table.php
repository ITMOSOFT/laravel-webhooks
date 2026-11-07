<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');

        Schema::create($prefix.'webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->text('secret');
            $table->json('events');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('webhooks.table_prefix', 'itmosoft_');
        Schema::dropIfExists($prefix.'webhook_endpoints');
    }
};
