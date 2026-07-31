<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: the snapshot pivot table is named "treatment_fee" (singular).
        Schema::table('treatment_fee', function (Blueprint $table) {
            $table->string('fee_group')->default('treatment')->after('category');
        });

        DB::table('treatment_fee')->whereIn('category', ['scanner', 'service', 'xray'])->update(['fee_group' => 'service']);
    }

    public function down(): void
    {
        Schema::table('treatment_fee', function (Blueprint $table) {
            $table->dropColumn('fee_group');
        });
    }
};
