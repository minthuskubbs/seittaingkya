<?php

namespace App\Models\Concerns;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the currently authenticated user's clinic.
 *
 * - Super admins (no clinic_id) see every clinic's rows.
 * - Any other user only ever sees / creates rows for their own clinic.
 * - New records get clinic_id filled automatically from the current user.
 */
trait BelongsToClinic
{
    public static function bootBelongsToClinic(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder) {
            $user = auth()->user();
            if ($user && $user->clinic_id) {
                $builder->where($builder->getModel()->getTable().'.clinic_id', $user->clinic_id);
            }
        });

        static::creating(function ($model) {
            if (! empty($model->clinic_id)) {
                return;
            }
            $user = auth()->user();
            // Clinic-bound users inherit their own clinic. A super admin (no clinic)
            // supplies one via the form's clinic_id field, falling back to the
            // "working clinic" they have selected in the topbar (session).
            $model->clinic_id = $user?->clinic_id ?: request('clinic_id') ?: session('active_clinic_id');
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /** Query without the clinic global scope (super admin / reporting use). */
    public function scopeAllClinics(Builder $query): Builder
    {
        return $query->withoutGlobalScope('clinic');
    }
}
