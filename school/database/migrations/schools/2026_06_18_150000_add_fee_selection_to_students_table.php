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
        Schema::table('students', function (Blueprint $table) {
            $table->tinyInteger('apply_class_fee')->default(1)->after('session_year_id');
            $table->tinyInteger('apply_van_fee')->default(0)->after('apply_class_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['apply_class_fee', 'apply_van_fee']);
        });
    }
};
