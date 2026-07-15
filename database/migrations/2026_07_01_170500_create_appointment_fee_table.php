<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot of a fee at the moment it was attached to an appointment.
        // This is what preserves old prices: updating fees.price later does NOT
        // change these rows, so historical totals stay intact.
        Schema::create('appointment_fee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->nullable()->constrained('fees')->nullOnDelete();
            $table->string('name');            // snapshot of fee name
            $table->string('category')->default('service');
            $table->decimal('price', 12, 2)->default(0); // snapshot price (0 when FOC)
            $table->boolean('is_foc')->default(false);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_fee');
    }
};
