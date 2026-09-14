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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mother_name')) {
                $table->string('mother_name', 191)->nullable()->after('aadhar_pic');
            }
            if (!Schema::hasColumn('users', 'mother_mobile')) {
                $table->string('mother_mobile', 191)->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('users', 'mother_image')) {
                $table->string('mother_image', 512)->nullable()->after('mother_mobile');
            }
            if (!Schema::hasColumn('users', 'mother_aadhar_pic')) {
                $table->string('mother_aadhar_pic', 191)->nullable()->after('mother_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mother_name', 'mother_mobile', 'mother_image', 'mother_aadhar_pic']);
        });
    }
};
