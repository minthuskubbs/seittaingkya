<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('appointment_no')->nullable()->index();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('status')->default('booked'); // booked | completed | cancelled | no_show
            $table->string('reason')->nullable();
            // Reappointment / follow-up chain.
            $table->foreignId('parent_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->dateTime('reminder_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->text('doctor_note')->nullable();
            $table->text('assistance_note')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
