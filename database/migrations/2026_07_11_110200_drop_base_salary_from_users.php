<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff payroll now lives in its own table; users no longer carry a salary.
        if (Schema::hasColumn('users', 'base_salary')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('base_salary'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'base_salary')) {
            Schema::table('users', fn (Blueprint $t) => $t->decimal('base_salary', 12, 2)->default(0)->after('is_active'));
        }
    }
};
