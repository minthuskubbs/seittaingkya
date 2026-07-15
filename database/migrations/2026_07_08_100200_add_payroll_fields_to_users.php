<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Monthly base salary (staff & doctors) and per-appointment commission
            // percentage (doctors). Commission is applied to the doctor's billed
            // appointment amounts for the month.
            $table->decimal('base_salary', 12, 2)->default(0)->after('is_active');
            $table->decimal('commission_percent', 5, 2)->default(0)->after('base_salary');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'commission_percent']);
        });
    }
};
