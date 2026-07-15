<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill: existing (global) fees are replicated for every clinic so no
        // clinic is left without its charge catalogue. Originals go to the first
        // clinic (keeping their ids, so historical appointment_fee snapshots stay
        // valid); copies are inserted for the remaining clinics.
        $clinics = DB::table('clinics')->orderBy('id')->pluck('id');
        $globalFees = DB::table('fees')->whereNull('clinic_id')->get();

        if ($clinics->isNotEmpty() && $globalFees->isNotEmpty()) {
            $first = $clinics->first();
            foreach ($clinics as $clinicId) {
                foreach ($globalFees as $fee) {
                    if ($clinicId === $first) {
                        DB::table('fees')->where('id', $fee->id)->update(['clinic_id' => $clinicId]);
                    } else {
                        DB::table('fees')->insert([
                            'clinic_id' => $clinicId,
                            'name' => $fee->name,
                            'category' => $fee->category,
                            'price' => $fee->price,
                            'is_foc' => $fee->is_foc,
                            'is_active' => $fee->is_active,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};
