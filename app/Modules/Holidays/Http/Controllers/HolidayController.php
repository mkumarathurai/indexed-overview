<?php

namespace App\Modules\Holidays\Http\Controllers;

use App\Http\Controllers\Controller;

class HolidayController extends Controller
{
    public function index()
    {
        return view('holidays::index');
    }
} 