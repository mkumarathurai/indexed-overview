<?php

namespace App\Modules\Holidays\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayWorklog extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'hours',
        'description'
    ];

    protected $casts = [
        'date' => 'datetime',
        'hours' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}