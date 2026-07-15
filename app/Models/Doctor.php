<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id', 'name', 'phone', 'specialization',
        'one_day_salary', 'commission_percent', 'is_active',
    ];

    protected $casts = [
        'one_day_salary' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(DoctorPayroll::class);
    }
}
