<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default pay values kept on the staff record; payroll pre-fills from these
        // each month and the super admin can still adjust per month. Bonus stays
        // payroll-only (entered fresh each month).
        Schema::table('staff', function (Blueprint $table) {
            $table->decimal('basic_salary', 12, 2)->default(0)->after('position');
            $table->decimal('attendance_allowance', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('transportation_allowance', 12, 2)->default(0)->after('attendance_allowance');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'attendance_allowance', 'transportation_allowance']);
        });
    }
};
