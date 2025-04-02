<?php

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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('path');
            $table->text('request_headers')->nullable();
            $table->text('request_body')->nullable();
            $table->integer('status_code');
            $table->text('response_headers')->nullable();
            $table->json('response_body')->nullable();
            $table->float('latency_ms');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index('path');
            $table->index('status_code');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
