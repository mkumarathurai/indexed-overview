<?php

namespace App\Modules\Holidays\Livewire;

use Livewire\Component;
use App\Modules\Holidays\Models\Holiday;
use App\Modules\Employees\Models\Employee;

class HolidayInfo extends Component
{
    public Employee $employee;
    public ?Holiday $holiday;

    public function mount(Employee $employee)
    {
        $this->employee = $employee;
        $this->holiday = Holiday::where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->first();
    }

    public function render()
    {
        return view('holidays::livewire.holiday-info');
    }
} 