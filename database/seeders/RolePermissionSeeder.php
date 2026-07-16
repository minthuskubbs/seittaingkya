<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Roles:
     *  - super_admin      : everything (all clinics, finance, product/stock setup, doctors, staff, reports)
     *  - clinic_admin     : appointments, patients, stock-OUT only, medicine sales. No finance/reports/product CRUD/doctors/staff.
     *  - assistance_admin : stock-OUT only, medicine sales, patient view. No finance/reports/product CRUD/doctors/staff.
     */
    public function run(): void
    {
        $permissions = [
            // Patients / clinical
            'patients.view', 'patients.manage',
            'doctors.manage',
            'staff.manage',
            'appointments.view', 'appointments.manage',
            'clinical.view', 'clinical.manage',
            'doctor_notes.manage',
            // Inventory
            'inventory.view', 'inventory.manage', 'inventory.stockout',
            'suppliers.manage',
            // POS / billing
            'pos.use', 'billing.view', 'billing.manage',
            // Fees catalogue (super admin owns create/edit/delete; others just select)
            'fees.manage',
            'procedures.manage',
            // Finance (super admin only)
            'finance.view',
            // Reports (super admin only)
            'reports.view',
            // Administration
            'users.manage', 'clinics.manage', 'audit.view', 'backup.manage',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p);
        }

        $superAdmin = Role::findOrCreate('super_admin');
        $superAdmin->syncPermissions(Permission::all());

        // Clinic admin & Assistance admin share the SAME day-to-day permissions:
        // patients, appointments, treatments/billing, doctor notes, stock-out and
        // medicine sales. No finance, reports, product CRUD, suppliers, doctors or
        // staff — those stay super-admin only.
        $sharedStaffPermissions = [
            'patients.view', 'patients.manage',
            'appointments.view', 'appointments.manage',
            'clinical.view', 'clinical.manage',
            'doctor_notes.manage',
            'inventory.view', 'inventory.stockout',
            'pos.use', 'billing.view', 'billing.manage',
        ];

        Role::findOrCreate('clinic_admin')->syncPermissions($sharedStaffPermissions);
        Role::findOrCreate('assistance_admin')->syncPermissions($sharedStaffPermissions);
    }
}
