<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Console\Command;

/**
 * Delete appointment test data. Deleting an appointment cascades its fee lines
 * and nulls the appointment_id on any linked treatments/prescriptions (those are
 * kept). Appointment payments are polymorphic, so they are removed explicitly.
 *
 *   php artisan appointments:clear            # all appointments
 *   php artisan appointments:clear --clinic=1 # only clinic 1
 *   php artisan appointments:clear --force    # skip the confirmation
 */
class ClearAppointments extends Command
{
    protected $signature = 'appointments:clear
        {--clinic= : Only clear appointments for this clinic id}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Delete appointments (with their fee lines and payments) — keeps treatments';

    public function handle(): int
    {
        $clinic = $this->option('clinic');

        $query = fn () => Appointment::withoutGlobalScope('clinic')
            ->when($clinic, fn ($q) => $q->where('clinic_id', (int) $clinic));

        $count = $query()->count();
        if ($count === 0) {
            $this->info('No appointments to clear.');

            return self::SUCCESS;
        }

        $scope = $clinic ? " for clinic {$clinic}" : '';
        $this->warn("This will delete {$count} appointment(s){$scope}, plus their fee lines and payments.");
        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        $ids = $query()->pluck('id');

        // Appointment payments are polymorphic (no cascade FK) — remove them first.
        $paid = Payment::where('payable_type', (new Appointment)->getMorphClass())
            ->whereIn('payable_id', $ids)->delete();

        // Deleting the appointments cascades appointment_fee rows and nulls the
        // appointment_id on linked treatments/prescriptions (they are preserved).
        $deleted = $query()->delete();

        $this->info("Deleted {$deleted} appointment(s) and {$paid} appointment payment(s). Linked treatments were kept.");

        return self::SUCCESS;
    }
}
