<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Diagnose and fix why products (medicines) don't appear in the POS / treatment
 * dropdowns. A dropdown shows a product only when it is active, of type
 * "medicine", and belongs to the viewing user's clinic — so products created
 * under the wrong clinic are invisible to the other clinic's staff.
 *
 *   php artisan products:audit                 # list every product and its clinic
 *   php artisan products:audit --move=2:1      # move all products from clinic 2 to clinic 1
 *   php artisan products:audit --copy=1:2      # copy clinic 1's products into clinic 2 (stock 0)
 */
class AuditProducts extends Command
{
    protected $signature = 'products:audit
        {--move= : Move products between clinics, formatted from:to (e.g. 2:1)}
        {--copy= : Copy products from one clinic to another, formatted from:to (e.g. 1:2)}';

    protected $description = 'List products with their clinic/type/status, and optionally move or copy them between clinics';

    public function handle(): int
    {
        if ($move = $this->option('move')) {
            [$from, $to] = $this->pair($move);
            if ($from === null) {
                return self::FAILURE;
            }
            $n = Product::withoutGlobalScope('clinic')->where('clinic_id', $from)->update(['clinic_id' => $to]);
            $this->info("Moved {$n} product(s) from clinic {$from} to clinic {$to}.");
        }

        if ($copy = $this->option('copy')) {
            [$from, $to] = $this->pair($copy);
            if ($from === null) {
                return self::FAILURE;
            }
            $existing = Product::withoutGlobalScope('clinic')->where('clinic_id', $to)->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
            $copied = 0;
            foreach (Product::withoutGlobalScope('clinic')->where('clinic_id', $from)->get() as $p) {
                if (in_array(mb_strtolower($p->name), $existing, true)) {
                    continue; // already present in the target clinic
                }
                $clone = $p->replicate(['stock_qty']);
                $clone->clinic_id = $to;
                $clone->stock_qty = 0; // stock is per-clinic; start empty and stock-in separately
                $clone->save();
                $copied++;
            }
            $this->info("Copied {$copied} product(s) from clinic {$from} to clinic {$to} (stock starts at 0).");
        }

        $rows = Product::withoutGlobalScope('clinic')->with('clinic')->orderBy('clinic_id')->orderBy('name')->get()
            ->map(fn ($p) => [
                $p->id,
                $p->name,
                $p->clinic->name ?? $p->clinic_id,
                $p->type,
                $p->is_active ? 'active' : 'inactive',
                $p->stock_qty,
            ]);

        $this->table(['ID', 'Name', 'Clinic', 'Type', 'Status', 'Stock'], $rows);
        $this->line('A product shows in a POS/treatment medicine dropdown only if: active, type = medicine, and in the viewer\'s clinic.');

        return self::SUCCESS;
    }

    /** Parse a "from:to" clinic pair; returns [null, null] and prints an error if invalid. */
    private function pair(string $value): array
    {
        [$from, $to] = array_pad(explode(':', $value), 2, null);
        if (! $from || ! $to) {
            $this->error('Value must be formatted from:to (e.g. 2:1)');

            return [null, null];
        }

        return [(int) $from, (int) $to];
    }
}
