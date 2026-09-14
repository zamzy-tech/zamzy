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
        Schema::table('school_inquiries', function (Blueprint $table) {
            $table->string('domain', 191)->nullable()->after('school_tagline');
            $table->string('domain_type', 50)->nullable()->default('default')->after('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_inquiries', function (Blueprint $table) {
            $table->dropColumn(['domain', 'domain_type']);
        });
    }
};
