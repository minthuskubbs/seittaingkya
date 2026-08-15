<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill: existing global types belong to the first clinic; clone them
        // for every other clinic so each clinic starts with the same list and can
        // then set its own prices.
        $clinics = DB::table('clinics')->orderBy('id')->pluck('id');
        if ($clinics->isNotEmpty()) {
            $first = $clinics->first();
            DB::table('treatment_types')->whereNull('clinic_id')->update(['clinic_id' => $first]);

            $originals = DB::table('treatment_types')->where('clinic_id', $first)->get();
            foreach ($clinics->slice(1) as $clinicId) {
                foreach ($originals as $o) {
                    DB::table('treatment_types')->insert([
                        'clinic_id' => $clinicId,
                        'name' => $o->name,
                        'price' => $o->price,
                        'require_qty' => $o->require_qty,
                        'sort_order' => $o->sort_order,
                        'is_active' => $o->is_active,
                        'created_at' => $o->created_at,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('clinic_id');
        });
    }
};
