<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });

        // client_uuid lets the offline queue replay a create exactly once:
        // if a row with the same uuid already exists we skip re-inserting.
        foreach (['patients', 'appointments', 'sales'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->uuid('client_uuid')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
        });
        foreach (['patients', 'appointments', 'sales'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropUnique([$t.'_client_uuid_unique']);
                $table->dropColumn('client_uuid');
            });
        }
    }
};
