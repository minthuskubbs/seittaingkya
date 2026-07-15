<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LowStockNotifier
{
    /**
     * Notify the staff responsible for a clinic's inventory that a product is low.
     * Recipients: super admins + the clinic's admins & assistants.
     */
    public function notify(Product $product): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($q) use ($product) {
                $q->whereHas('roles', fn ($r) => $r->where('name', 'super_admin'))
                  ->orWhere(function ($w) use ($product) {
                      $w->where('clinic_id', $product->clinic_id)
                        ->whereHas('roles', fn ($r) => $r->whereIn('name', ['clinic_admin', 'assistance_admin']));
                  });
            })
            ->get();

        try {
            Notification::send($recipients, new LowStockNotification($product));
        } catch (\Throwable $e) {
            // Web push can fail (e.g. OpenSSL/VAPID); DB notifications still record.
            Log::warning('Low stock notification partially failed: '.$e->getMessage());
        }
    }
}
