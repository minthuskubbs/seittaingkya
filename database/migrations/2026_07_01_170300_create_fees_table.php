<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogue of charges maintained by the super admin (dentist fee, x-ray,
        // scanner, service charge, etc.). Prices here are the "current" prices;
        // historical appointments keep the price snapshotted at selection time.
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('service'); // service | xray | scanner | dentist | other
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_foc')->default(false); // free of charge -> price treated as 0
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
