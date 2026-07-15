<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayroll extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'staff_id', 'clinic_id', 'year', 'month',
        'basic_salary', 'bonus', 'attendance_allowance', 'transportation_allowance',
        'total', 'created_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'attendance_allowance' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
