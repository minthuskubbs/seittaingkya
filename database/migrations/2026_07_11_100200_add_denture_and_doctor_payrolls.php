<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Denture / prosthesis charge captured per appointment (deducted before
        // the doctor's commission is calculated).
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('denture_charge', 12, 2)->default(0)->after('total_amount');
        });

        // One saved payroll run per doctor per month. days_worked is entered by
        // the admin; the rest is computed and snapshotted for the record.
        Schema::create('doctor_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('days_worked')->default(0);
            $table->decimal('one_day_salary', 12, 2)->default(0);   // snapshot
            $table->decimal('commission_percent', 5, 2)->default(0); // snapshot
            $table->decimal('total_income', 12, 2)->default(0);      // sum of appointment totals
            $table->decimal('denture_total', 12, 2)->default(0);     // sum of denture charges
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['doctor_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_payrolls');
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('denture_charge');
        });
    }
};
