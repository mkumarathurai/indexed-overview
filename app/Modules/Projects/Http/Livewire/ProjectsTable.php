<?php

namespace App\Modules\Projects\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;

class ProjectsTable extends Component
{
    public $projects = [];
    public $sortField = 'key';
    public $sortDirection = 'asc';

    public function mount($projects)
    {
        $this->projects = collect($projects)->toArray();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        usort($this->projects, function ($a, $b) use ($field) {
            $valueA = is_numeric($a[$field]) ? floatval($a[$field]) : $a[$field];
            $valueB = is_numeric($b[$field]) ? floatval($b[$field]) : $b[$field];
            
            if ($valueA == $valueB) {
                return 0;
            }
            
            $comparison = $valueA <=> $valueB;
            return $this->sortDirection === 'asc' ? $comparison : -$comparison;
        });
    }

    public function render()
    {
        return view('projects::livewire.projects-table');
    }
}
