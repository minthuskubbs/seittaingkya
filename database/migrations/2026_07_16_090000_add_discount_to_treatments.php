<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            // percent = value is a %, fixed = value is a flat MMK amount.
            $table->string('discount_type')->default('fixed')->after('total_amount');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
