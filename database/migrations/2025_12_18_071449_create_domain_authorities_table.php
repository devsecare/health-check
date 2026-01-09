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
        Schema::create('domain_authorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->integer('domain_authority')->nullable(); // Domain Authority score (0-100)
            $table->integer('page_authority')->nullable(); // Page Authority score (0-100)
            $table->integer('spam_score')->nullable(); // Spam Score (0-100)
            $table->integer('backlinks')->nullable(); // Number of backlinks
            $table->integer('referring_domains')->nullable(); // Number of referring domains
            $table->longText('raw_data')->nullable(); // Raw API response data
            $table->timestamps();

            // Indexes
            $table->index(['website_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_authorities');
    }
};
