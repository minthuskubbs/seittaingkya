<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;

/**
 * For super admins (who belong to no single clinic) this guarantees a
 * "working clinic" is always selected in the session, so any new record they
 * create has a clinic to belong to. Clinic-bound users are unaffected.
 */
class SetActiveClinic
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->clinic_id && ! session('active_clinic_id')) {
            session(['active_clinic_id' => Clinic::where('is_active', true)->value('id')]);
        }

        return $next($request);
    }
}
