<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Treatment types (Tx-names) — scaling, filling, extraction, etc. A treatment
        // can have several, selected via checkboxes on the treatment form.
        Schema::create('treatment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('treatment_treatment_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_type_id')->constrained()->cascadeOnDelete();
            $table->unique(['treatment_id', 'treatment_type_id'], 'treatment_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_treatment_type');
        Schema::dropIfExists('treatment_types');
    }
};
