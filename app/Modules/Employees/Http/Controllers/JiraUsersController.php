<?php

namespace App\Modules\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JiraUsersController extends Controller
{
    /**
     * Display a listing of all Jira users.
     */
    public function index()
    {
        return view('employees::jira-users-index');
    }
} 