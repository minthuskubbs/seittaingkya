<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Polymorphic payments so both appointments (patient billing) and sales
        // (medicine POS) can record multiple payments / multiple methods.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->morphs('payable'); // appointment | sale
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cash'); // cash | kbzpay | wavepay | card | bank
            $table->string('reference')->nullable();
            $table->date('paid_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
