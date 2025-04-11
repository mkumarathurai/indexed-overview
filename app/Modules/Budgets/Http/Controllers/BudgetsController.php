<?php

namespace App\Modules\Budgets\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BudgetsController extends Controller
{
    public function index()
    {
        return view('budgets::budgets-index');
    }
} 