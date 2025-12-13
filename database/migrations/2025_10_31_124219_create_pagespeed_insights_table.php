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
        Schema::create('pagespeed_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('strategy')->default('mobile'); // mobile or desktop
            
            // Scores (0-100)
            $table->integer('performance_score')->nullable();
            $table->integer('accessibility_score')->nullable();
            $table->integer('seo_score')->nullable();
            $table->integer('best_practices_score')->nullable();
            
            // Core Web Vitals
            $table->decimal('lcp', 10, 2)->nullable(); // Largest Contentful Paint (ms)
            $table->decimal('fcp', 10, 2)->nullable(); // First Contentful Paint (ms)
            $table->decimal('cls', 10, 2)->nullable(); // Cumulative Layout Shift
            $table->decimal('tbt', 10, 2)->nullable(); // Total Blocking Time (ms)
            $table->decimal('si', 10, 2)->nullable(); // Speed Index
            
            // Additional metrics
            $table->decimal('ttfb', 10, 2)->nullable(); // Time to First Byte (ms)
            $table->decimal('interactive', 10, 2)->nullable(); // Time to Interactive (ms)
            
            // Raw JSON data for detailed results
            $table->longText('raw_data')->nullable();
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['website_id', 'strategy', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagespeed_insights');
    }
};
