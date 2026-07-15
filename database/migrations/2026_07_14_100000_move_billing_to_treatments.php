<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Treatments become the billing record: charges, extra amounts and per-tooth
        // fees live here (moved off the appointment).
        Schema::table('treatments', function (Blueprint $table) {
            $table->decimal('denture_charge', 12, 2)->default(0)->after('notes');
            $table->decimal('surgery_charge', 12, 2)->default(0)->after('denture_charge');
            $table->decimal('additional_charge', 12, 2)->default(0)->after('surgery_charge');
            // Per-tooth fixed fees: unit price x quantity.
            $table->decimal('extraction_price', 12, 2)->default(0)->after('additional_charge');
            $table->unsignedInteger('extraction_qty')->default(0)->after('extraction_price');
            $table->decimal('implant_price', 12, 2)->default(0)->after('extraction_qty');
            $table->unsignedInteger('implant_qty')->default(0)->after('implant_price');
            $table->decimal('total_amount', 12, 2)->default(0)->after('implant_qty');
            $table->text('doctor_feedback')->nullable()->after('total_amount');
        });

        // Snapshot of catalogue fees attached to a treatment (price frozen at selection).
        Schema::create('treatment_fee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->nullable()->constrained('fees')->nullOnDelete();
            $table->string('name');
            $table->string('category')->default('service');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_foc')->default(false);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });

        // A medicine sale can optionally belong to a treatment (combined invoice).
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('treatment_id')->nullable()->after('patient_id')->constrained('treatments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->dropColumn('treatment_id');
        });
        Schema::dropIfExists('treatment_fee');
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropColumn([
                'denture_charge', 'surgery_charge', 'additional_charge',
                'extraction_price', 'extraction_qty', 'implant_price', 'implant_qty',
                'total_amount', 'doctor_feedback',
            ]);
        });
    }
};
