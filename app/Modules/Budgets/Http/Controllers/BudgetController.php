<?php

namespace App\Modules\Budgets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Budgets\Models\Budget;

class BudgetController extends Controller
{
    public function index()
    {
        return view('budgets::index');
    }
}
