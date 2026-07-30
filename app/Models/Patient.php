<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Patient extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'client_uuid', 'patient_code', 'name', 'age', 'address', 'phone',
        'doctor_desc', 'assistance_desc', 'assigned_doctor_id',
        'medical_condition', 'drug_allergy', 'diabetes', 'hypertension',
    ];

    protected $casts = ['diabetes' => 'boolean', 'hypertension' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->patient_code)) {
                $patient->patient_code = 'P'.str_pad((string) (self::withoutGlobalScope('clinic')->max('id') + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function assignedDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(DoctorFeedback::class)->latest();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function xrays(): MorphMany
    {
        return $this->attachments()->where('category', 'xray');
    }

    public function documents(): MorphMany
    {
        return $this->attachments()->where('category', 'document');
    }
}
