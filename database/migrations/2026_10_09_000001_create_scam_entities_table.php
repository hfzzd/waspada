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
        Schema::create('scam_entities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['phone', 'bank_account', 'ewallet', 'url', 'email', 'other']);
            $table->string('value');
            $table->string('normalized_value');
            $table->enum('verdict', ['aman', 'hati_hati', 'bahaya'])->default('hati_hati');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->timestamp('first_reported_at')->nullable();
            $table->timestamp('last_reported_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['type', 'normalized_value']);
            $table->index('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scam_entities');
    }
};
