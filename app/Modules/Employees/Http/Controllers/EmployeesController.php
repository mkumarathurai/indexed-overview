<?php

namespace App\Modules\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\JiraClient;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Cache;

class EmployeesController extends Controller
{
    protected $jiraClient;

    public function __construct()
    {
        $this->jiraClient = new JiraClient(
            config('services.jira.api_token'),
            config('services.jira.base_url')
        );
    }

    public function index()
    {
        $employees = Cache::remember('indexed_employees', 3600, function () {
            try {
                $response = $this->jiraClient->get('/rest/api/3/users');
                
                return collect($response->json())
                    ->filter(function ($user) {
                        return isset($user['emailAddress']) && 
                               str_ends_with($user['emailAddress'], '@indexed.dk') &&
                               ($user['active'] ?? false);
                    });
            } catch (\Exception $e) {
                return collect();
            }
        });

        return view('employees::index', compact('employees'));
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        return view('employees::employee-details', compact('employee'));
    }

    protected function syncEmployeeFromJira($jiraUser)
    {
        return Employee::updateOrCreate(
            ['email' => $jiraUser['emailAddress']],
            [
                'name' => $jiraUser['displayName'],
                'avatar' => $jiraUser['avatarUrls']['48x48'] ?? null,
                'external_url' => $jiraUser['self'] ?? null,
            ]
        );
    }
}