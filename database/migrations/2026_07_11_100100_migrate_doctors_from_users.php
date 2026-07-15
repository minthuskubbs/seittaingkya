<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Columns that referenced a doctor as a user.
    private array $doctorColumns = [
        ['appointments', 'doctor_id'],
        ['treatments', 'doctor_id'],
        ['sales', 'doctor_id'],
        ['prescriptions', 'doctor_id'],
        ['patients', 'assigned_doctor_id'],
    ];

    public function up(): void
    {
        // 1. Build old-user-id -> new-doctor-id map from users holding the doctor role.
        $doctorRoleId = DB::table('roles')->where('name', 'doctor')->value('id');
        $doctorUserIds = $doctorRoleId
            ? DB::table('model_has_roles')->where('role_id', $doctorRoleId)
                ->where('model_type', User::class)->pluck('model_id')->all()
            : [];

        $map = []; // old user id => new doctor id
        foreach (DB::table('users')->whereIn('id', $doctorUserIds)->get() as $u) {
            $map[$u->id] = DB::table('doctors')->insertGetId([
                'clinic_id' => $u->clinic_id,
                'name' => $u->name,
                'phone' => $u->phone,
                'one_day_salary' => 0,
                'commission_percent' => $u->commission_percent ?? 0,
                'is_active' => $u->is_active ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Re-point each doctor_id column from users to doctors. An offset avoids
        //    id collisions while remapping in place (old user ids vs new doctor ids).
        $offset = 1000000;
        foreach ($this->doctorColumns as [$table, $column]) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));

            foreach ($map as $old => $new) {
                DB::table($table)->where($column, $old)->update([$column => $new + $offset]);
            }
            // Any leftover non-null value did not map to a doctor -> clear it.
            DB::table($table)->whereNotNull($column)->where($column, '<', $offset)->update([$column => null]);
            // Bring the offset values back down to the real doctor ids.
            foreach ($map as $new) {
                DB::table($table)->where($column, $new + $offset)->update([$column => $new]);
            }

            Schema::table($table, fn (Blueprint $t) => $t->foreign($column)->references('id')->on('doctors')->nullOnDelete());
        }

        // 3. Doctor notes were editable by the doctor role; hand that to clinic admins.
        $clinicAdminRoleId = DB::table('roles')->where('name', 'clinic_admin')->value('id');
        $notePermId = DB::table('permissions')->where('name', 'doctor_notes.manage')->value('id');
        if ($clinicAdminRoleId && $notePermId) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $clinicAdminRoleId, 'permission_id' => $notePermId]
            );
        }

        // 4. Remove the doctor users and the doctor role entirely.
        if (! empty($map)) {
            DB::table('model_has_roles')->whereIn('model_id', array_keys($map))
                ->where('model_type', User::class)->delete();
            DB::table('users')->whereIn('id', array_keys($map))->delete();
        }
        if ($doctorRoleId) {
            DB::table('role_has_permissions')->where('role_id', $doctorRoleId)->delete();
            DB::table('roles')->where('id', $doctorRoleId)->delete();
        }

        // 5. Commission now lives on doctors, not users.
        if (Schema::hasColumn('users', 'commission_percent')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('commission_percent'));
        }
    }

    public function down(): void
    {
        // One-way data migration; re-point columns back to users and restore column.
        foreach ($this->doctorColumns as [$table, $column]) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
                $t->foreign($column)->references('id')->on('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('users', 'commission_percent')) {
            Schema::table('users', fn (Blueprint $t) => $t->decimal('commission_percent', 5, 2)->default(0)->after('base_salary'));
        }
    }
};
