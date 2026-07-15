<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use BelongsToClinic;

    protected $table = 'staff';

    protected $fillable = [
        'clinic_id', 'name', 'phone', 'position', 'is_active',
        'basic_salary', 'attendance_allowance', 'transportation_allowance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'basic_salary' => 'decimal:2',
        'attendance_allowance' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
    ];

    public function payrolls(): HasMany
    {
        return $this->hasMany(StaffPayroll::class);
    }
}
