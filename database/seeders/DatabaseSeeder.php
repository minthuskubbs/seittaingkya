<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Fee;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Two clinics.
        $clinic1 = Clinic::firstOrCreate(['code' => 'C1'], [
            'name' => 'Clinic 1', 'phone' => '', 'address' => '',
        ]);
        $clinic2 = Clinic::firstOrCreate(['code' => 'C2'], [
            'name' => 'Clinic 2', 'phone' => '', 'address' => '',
        ]);

        // Super admin (not tied to a clinic).
        $super = User::firstOrCreate(
            ['email' => 'superadmin@dental.local'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'clinic_id' => null]
        );
        $super->syncRoles('super_admin');

        // A clinic admin + doctor + assistant for clinic 1 (demo accounts).
        $ca = User::firstOrCreate(
            ['email' => 'admin1@dental.local'],
            ['name' => 'Clinic 1 Admin', 'password' => Hash::make('password'), 'clinic_id' => $clinic1->id]
        );
        $ca->syncRoles('clinic_admin');

        $asst = User::firstOrCreate(
            ['email' => 'assist1@dental.local'],
            ['name' => 'Assistant 1', 'password' => Hash::make('password'), 'clinic_id' => $clinic1->id]
        );
        $asst->syncRoles('assistance_admin');

        // Sample fees per clinic (super admin managed). [name, category, price, foc, group]
        $fees = [
            ['Dentist Fee', 'dentist', 10000, false, 'treatment'],
            ['Dentist Fee (FOC)', 'dentist', 0, true, 'treatment'],
            ['Patient Service Charge', 'service', 5000, false, 'service'],
            ['X-ray Charge', 'xray', 8000, false, 'service'],
            ['Scanner Fee', 'scanner', 15000, false, 'service'],
        ];
        foreach ([$clinic1, $clinic2] as $clinic) {
            foreach ($fees as [$name, $cat, $price, $foc, $group]) {
                Fee::withoutGlobalScope('clinic')->firstOrCreate(
                    ['clinic_id' => $clinic->id, 'name' => $name],
                    ['category' => $cat, 'fee_group' => $group, 'price' => $price, 'is_foc' => $foc, 'is_active' => true]
                );
            }
        }

        // Sale types (super admin managed).
        foreach ([['Walk In', 'walk_in', 1], ['Doctor', 'doctor', 2], ['Other', 'other', 3]] as [$n, $s, $o]) {
            \App\Models\SaleType::firstOrCreate(['slug' => $s], ['name' => $n, 'sort_order' => $o, 'is_active' => true]);
        }

        // Expense types (super admin managed).
        foreach ([['Salary', 1], ['Bill', 2], ['Rent', 3], ['Utilities', 4], ['Supplies', 5], ['Other', 6]] as [$n, $o]) {
            \App\Models\ExpenseType::firstOrCreate(['name' => $n], ['sort_order' => $o, 'is_active' => true]);
        }

        // Tooth charge types (extraction / implant classes) — super admin managed.
        foreach ([
            ['extraction', 'Simple', 10000, 1], ['extraction', 'Surgical', 30000, 2], ['extraction', 'Wisdom Tooth', 50000, 3],
            ['implant', 'Class A', 500000, 1], ['implant', 'Class B', 700000, 2], ['implant', 'Class C', 900000, 3],
        ] as [$kind, $name, $price, $o]) {
            \App\Models\ToothChargeType::firstOrCreate(
                ['kind' => $kind, 'name' => $name],
                ['price' => $price, 'sort_order' => $o, 'is_active' => true]
            );
        }

        // Treatment types (Tx-names) — super admin managed, multi-select on treatments.
        // [name, sort, price, require_qty]. Scaling is a flat charge (no qty).
        foreach ([
            ['Scaling', 1, 20000, false],
            ['Filling', 2, 25000, true],
            ['Root Canal', 4, 80000, true],
            ['Crown', 5, 150000, true],
            ['Whitening', 8, 100000, true],
        ] as [$n, $o, $price, $rq]) {
            \App\Models\TreatmentType::firstOrCreate(
                ['name' => $n],
                ['sort_order' => $o, 'price' => $price, 'require_qty' => $rq, 'is_active' => true]
            );
        }

        // Sample doctors (records, not login users). one_day_salary + commission %.
        foreach ([[$clinic1, 'Dr. Aung', 100000, 45], [$clinic2, 'Dr. May', 120000, 40]] as [$clinic, $name, $daily, $pct]) {
            \App\Models\Doctor::withoutGlobalScope('clinic')->firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => $name],
                ['one_day_salary' => $daily, 'commission_percent' => $pct, 'is_active' => true]
            );
        }

        // Sample staff (records for payroll) with default pay values.
        foreach ([
            [$clinic1, 'Ma Hla', 'Receptionist', 250000, 20000, 30000],
            [$clinic1, 'Ko Zaw', 'Nurse', 300000, 25000, 30000],
        ] as [$clinic, $name, $pos, $basic, $attend, $transport]) {
            \App\Models\Staff::withoutGlobalScope('clinic')->firstOrCreate(
                ['clinic_id' => $clinic->id, 'name' => $name],
                [
                    'position' => $pos, 'is_active' => true,
                    'basic_salary' => $basic, 'attendance_allowance' => $attend, 'transportation_allowance' => $transport,
                ]
            );
        }

        // Sample procedures.
        foreach ([
            ['Scaling', 'SCL', 20000],
            ['Tooth Extraction', 'EXT', 30000],
            ['Filling', 'FIL', 25000],
            ['Root Canal', 'RCT', 80000],
        ] as [$name, $code, $price]) {
            Procedure::firstOrCreate(['name' => $name], [
                'code' => $code, 'default_price' => $price, 'is_active' => true,
            ]);
        }
    }
}
