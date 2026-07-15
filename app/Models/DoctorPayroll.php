<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPayroll extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'doctor_id', 'clinic_id', 'year', 'month', 'days_worked',
        'one_day_salary', 'commission_percent', 'total_income', 'denture_total',
        'basic_salary', 'commission', 'total', 'created_by',
    ];

    protected $casts = [
        'one_day_salary' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'total_income' => 'decimal:2',
        'denture_total' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'commission' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
