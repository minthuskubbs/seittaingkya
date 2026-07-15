<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('patient_code')->nullable()->index();
            $table->string('name');
            $table->unsignedInteger('age')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();

            // Extended clinical info kept on the patient record.
            $table->text('doctor_desc')->nullable();
            $table->text('assistance_desc')->nullable();
            $table->foreignId('assigned_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('medical_condition')->nullable();
            $table->text('drug_allergy')->nullable();
            $table->boolean('diabetes')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
