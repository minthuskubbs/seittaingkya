<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Predefined denture types with prices; each can be tied to a supplier.
        Schema::create('denture_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('treatments', function (Blueprint $table) {
            $table->foreignId('denture_type_id')->nullable()->after('denture_charge')->constrained('denture_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['denture_type_id']);
            $table->dropColumn('denture_type_id');
        });
        Schema::dropIfExists('denture_types');
    }
};
