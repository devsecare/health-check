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
        Schema::create('broken_links_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('url');
            
            // Summary data (JSON)
            $table->json('summary')->nullable();
            
            // All broken links data (JSON)
            $table->json('broken_links_data')->nullable();
            
            $table->integer('total_checked')->default(0);
            $table->integer('total_broken')->default(0);
            
            // Raw check data
            $table->longText('raw_data')->nullable();
            
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
        Schema::dropIfExists('broken_links_checks');
    }
};
