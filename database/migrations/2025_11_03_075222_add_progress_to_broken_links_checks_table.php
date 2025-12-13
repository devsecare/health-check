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
        Schema::table('broken_links_checks', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('url');
            $table->string('status')->default('pending')->after('progress'); // pending, running, completed, failed
            $table->string('job_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broken_links_checks', function (Blueprint $table) {
            $table->dropColumn(['progress', 'status', 'job_id']);
        });
    }
};
