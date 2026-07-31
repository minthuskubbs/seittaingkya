<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('doctor_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropConstrainedForeignId('supplier_id');
        });
    }
};
