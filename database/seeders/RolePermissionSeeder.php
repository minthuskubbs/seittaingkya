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

        // Clinic admin: day-to-day operations only. No finance, reports, product
        // CRUD, suppliers, doctors, staff. Inventory is limited to stock-out.
        $clinicAdmin = Role::findOrCreate('clinic_admin');
        $clinicAdmin->syncPermissions([
            'patients.view', 'patients.manage',
            'appointments.view', 'appointments.manage',
            // Treatment is the billing record they add charges to.
            'clinical.view', 'clinical.manage',
            'doctor_notes.manage',
            'inventory.view', 'inventory.stockout',
            'pos.use', 'billing.view', 'billing.manage',
        ]);

        // Assistance admin: stock-out + medicine sales, appointments, and can view
        // treatments + add doctor feedback (but not edit charges).
        $assistanceAdmin = Role::findOrCreate('assistance_admin');
        $assistanceAdmin->syncPermissions([
            'patients.view',
            'appointments.view', 'appointments.manage',
            'clinical.view', 'doctor_notes.manage',
            'inventory.view', 'inventory.stockout',
            'pos.use', 'billing.view',
        ]);
    }
}
