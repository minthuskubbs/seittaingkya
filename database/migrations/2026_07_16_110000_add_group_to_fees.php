<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fees belong to a group: "treatment" (commissionable) or "service" (not).
        Schema::table('fees', function (Blueprint $table) {
            $table->string('fee_group')->default('treatment')->after('category');
        });

        // Backfill from category: scanner/service/xray are Services Fees.
        DB::table('fees')->whereIn('category', ['scanner', 'service', 'xray'])->update(['fee_group' => 'service']);
        DB::table('fees')->whereIn('category', ['dentist', 'other'])->update(['fee_group' => 'treatment']);
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('fee_group');
        });
    }
};
