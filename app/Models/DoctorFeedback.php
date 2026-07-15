<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorFeedback extends Model
{
    use BelongsToClinic;

    protected $table = 'doctor_feedbacks';

    protected $fillable = [
        'clinic_id', 'patient_id', 'doctor_id', 'treatment_id', 'note', 'created_by',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
