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
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('url');
            
            // Meta tags data (JSON)
            $table->json('meta_tags')->nullable();
            
            // Headings data (JSON)
            $table->json('headings')->nullable();
            
            // Images data (JSON)
            $table->json('images')->nullable();
            
            // URL structure data (JSON)
            $table->json('url_structure')->nullable();
            
            // Internal links data (JSON)
            $table->json('internal_links')->nullable();
            
            // Schema markup data (JSON)
            $table->json('schema_markup')->nullable();
            
            // Open Graph data (JSON)
            $table->json('open_graph')->nullable();
            
            // Robots.txt data (JSON)
            $table->json('robots_txt')->nullable();
            
            // Sitemap data (JSON)
            $table->json('sitemap')->nullable();
            
            // Overall score (0-100)
            $table->integer('overall_score')->nullable();
            
            // Raw audit data
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
        Schema::dropIfExists('seo_audits');
    }
};
