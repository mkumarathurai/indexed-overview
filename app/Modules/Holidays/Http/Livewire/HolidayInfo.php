<?php

namespace App\Modules\Holidays\Http\Livewire;

use Livewire\Component;
use App\Modules\Employees\Models\Employee;

class HolidayInfo extends Component
{
    public $employee;
    
    public function mount(Employee $employee)
    {
        $this->employee = $employee;
    }
    
    public function render()
    {
        return view('holidays::livewire.holiday-info', [
            'employee' => $this->employee
        ]);
    }
} 