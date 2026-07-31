<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Predefined tooth extraction / implant classes (A/B/C ...) with prices.
        Schema::create('tooth_charge_types', function (Blueprint $table) {
            $table->id();
            $table->string('kind'); // extraction | implant
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Selected type per treatment (price snapshotted into extraction_price / implant_price).
        Schema::table('treatments', function (Blueprint $table) {
            $table->foreignId('extraction_type_id')->nullable()->after('extraction_qty')->constrained('tooth_charge_types')->nullOnDelete();
            $table->foreignId('implant_type_id')->nullable()->after('implant_qty')->constrained('tooth_charge_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['extraction_type_id']);
            $table->dropForeign(['implant_type_id']);
            $table->dropColumn(['extraction_type_id', 'implant_type_id']);
        });
        Schema::dropIfExists('tooth_charge_types');
    }
};
