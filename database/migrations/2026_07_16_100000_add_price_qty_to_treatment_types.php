<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('name');
            // When false (e.g. Scaling) the type is a flat charge with no qty.
            $table->boolean('require_qty')->default(true)->after('price');
        });

        // Pivot carries the qty and a price snapshot per treatment.
        Schema::table('treatment_treatment_type', function (Blueprint $table) {
            $table->unsignedInteger('qty')->default(1)->after('treatment_type_id');
            $table->decimal('unit_price', 12, 2)->default(0)->after('qty');
            $table->decimal('line_total', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_treatment_type', function (Blueprint $table) {
            $table->dropColumn(['qty', 'unit_price', 'line_total']);
        });
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->dropColumn(['price', 'require_qty']);
        });
    }
};
