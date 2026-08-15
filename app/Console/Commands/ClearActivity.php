<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clear all activity/test data in one shot: patients, appointments, treatments,
 * medicine sales, payments, products and stock movements. Catalogues (fees, all
 * *_types, procedures), suppliers, doctors, staff, clinics and users are kept.
 *
 *   php artisan activity:clear          # asks to confirm
 *   php artisan activity:clear --force  # no prompt
 */
class ClearActivity extends Command
{
    protected $signature = 'activity:clear {--force : Skip the confirmation prompt}';

    protected $description = 'Clear activity data (patients, appointments, treatments, sales, payments, products, stock)';

    /** Activity tables to empty. Order does not matter — FK checks are disabled. */
    private array $tables = [
        'payments',
        'sale_items', 'sales',
        'stock_movements', 'products',
        'appointment_fee', 'appointments',
        'treatment_fee', 'treatment_treatment_type', 'treatments',
        'prescription_items', 'prescriptions', 'treatment_plans',
        'doctor_feedbacks', 'attachments',
        'patients',
    ];

    public function handle(): int
    {
        $counts = [];
        foreach ($this->tables as $t) {
            $counts[$t] = DB::table($t)->count();
        }

        $this->warn('This will DELETE all rows from these tables:');
        foreach ($counts as $t => $c) {
            $this->line(sprintf('  %-26s %d', $t, $c));
        }
        $this->newLine();
        $this->info('Kept: fees, all *_types, procedures, suppliers, doctors, staff, clinics, users.');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('This cannot be undone. Continue?')) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $t) {
            DB::table($t)->truncate();
        }
        Schema::enableForeignKeyConstraints();

        $this->info('Cleared all activity data (including products).');

        return self::SUCCESS;
    }
}
