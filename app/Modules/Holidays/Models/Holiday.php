<?php

namespace App\Modules\Holidays\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Employees\Models\Employee;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'employee_id',
        'date',
        'hours',
        'description'
    ];

    protected $casts = [
        'date' => 'datetime',
        'hours' => 'float'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}