<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Reset the system for going live: wipe all transactional/test data while
 * keeping the super-admin-managed type catalogues and structural tables, then
 * (re)create the super admin logins.
 *
 * The password is passed on the command line so it is never stored in the repo:
 *   php artisan app:reset-for-launch --password="YOUR_PASSWORD" --force
 */
class ResetForLaunch extends Command
{
    protected $signature = 'app:reset-for-launch
        {--password= : Password for the new super admin accounts (required)}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Wipe transactional data (keep type catalogues) and create the super admin accounts';

    /** Type catalogues + structural tables that must survive the wipe. */
    private array $keep = [
        'clinics', 'fees', 'procedures', 'sale_types', 'expense_types',
        'tooth_charge_types', 'denture_types', 'treatment_types', 'suppliers',
        'roles', 'permissions', 'role_has_permissions', 'migrations',
    ];

    /** The two super admin accounts to (re)create. */
    private array $superAdmins = [
        'admin@seittaingkya.com',
        'minthukyaw727@gmail.com',
    ];

    public function handle(): int
    {
        $password = (string) $this->option('password');
        if ($password === '') {
            $this->error('A password is required: --password="YOUR_PASSWORD"');

            return self::FAILURE;
        }

        // Every table except the ones we explicitly keep gets truncated.
        $all = array_map(
            fn ($row) => array_values((array) $row)[0],
            DB::select('SHOW TABLES')
        );
        $wipe = array_values(array_diff($all, $this->keep));
        sort($wipe);

        $this->warn('This will DELETE all data from these tables:');
        $this->line('  '.implode(', ', $wipe));
        $this->newline();
        $this->info('Kept (catalogues + structure): '.implode(', ', $this->keep));
        $this->newline();
        $this->info('Super admins to create: '.implode(', ', $this->superAdmins));
        $this->newline();

        if (! $this->option('force') && ! $this->confirm('This cannot be undone. Continue?')) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();
        foreach ($wipe as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();
        $this->info('Wiped '.count($wipe).' tables.');

        // Recreate the super admins (not tied to a clinic).
        foreach ($this->superAdmins as $email) {
            $user = User::create([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'clinic_id' => null,
            ]);
            $user->syncRoles('super_admin');
            $this->info("Created super admin: {$email}");
        }

        // Clear the spatie permission cache so new role links take effect.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->newline();
        $this->info('Done. The system is reset for launch.');

        return self::SUCCESS;
    }
}
