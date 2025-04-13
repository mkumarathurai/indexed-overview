<?php

namespace App\Modules\Employees\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employees\Services\JiraApiService;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncJiraUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jira:sync-users {--debug : Show debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Jira to the local database';

    /**
     * List of email domains that are considered to be employees
     * 
     * @var array
     */
    protected $employeeDomains = [
        'indexed.dk',
        // Add other employee domains here
    ];

    /**
     * List of account types to exclude (app accounts, etc.)
     * 
     * @var array
     */
    protected $excludedAccountTypes = [
        'app',
        'system'
    ];
    
    /**
     * Jira base URL
     * 
     * @var string
     */
    protected $baseUrl;
    
    /**
     * Constructor to initialize the command
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('services.jira.base_url');
    }

    /**
     * Execute the console command.
     */
    public function handle(JiraApiService $jiraService)
    {
        $this->info('Starting Jira users sync...');
        $debug = $this->option('debug');
        
        try {
            // Get all users
            $users = $jiraService->getUsers(0, 100);
            
            if ($debug) {
                $this->info('API Response: ' . json_encode(array_slice($users, 0, 2)));
            }
            
            $this->info('Retrieved ' . count($users) . ' users from Jira');
            
            if (empty($users)) {
                $this->warn('No users found in Jira API response. Check your authentication credentials.');
                return Command::FAILURE;
            }
            
            $syncedCount = 0;
            $employeeCount = 0;
            $externalCount = 0;
            $skippedCount = 0;
            
            foreach ($users as $user) {
                if (empty($user['accountId']) || empty($user['displayName'])) {
                    if ($debug) {
                        $this->warn('Skipping user due to missing accountId or displayName: ' . json_encode($user));
                    }
                    $skippedCount++;
                    continue;
                }
                
                // Skip inactive users
                if (isset($user['active']) && $user['active'] === false) {
                    if ($debug) {
                        $this->warn('Skipping inactive user: ' . $user['displayName']);
                    }
                    $skippedCount++;
                    continue;
                }
                
                // Skip excluded account types
                if (isset($user['accountType']) && in_array($user['accountType'], $this->excludedAccountTypes)) {
                    if ($debug) {
                        $this->warn('Skipping user with excluded account type: ' . $user['displayName'] . ' (' . $user['accountType'] . ')');
                    }
                    $skippedCount++;
                    continue;
                }
                
                $avatarUrl = isset($user['avatarUrls']) ? ($user['avatarUrls']['48x48'] ?? null) : null;
                $email = $user['emailAddress'] ?? null;
                
                // Determine if this is an employee based on email domain
                $type = 'external';
                if (!empty($email)) {
                    $emailDomain = substr($email, strpos($email, '@') + 1);
                    if (in_array($emailDomain, $this->employeeDomains)) {
                        $type = 'employee';
                    }
                }
                
                // Get the user's title if available
                $title = null;
                if ($type === 'employee' && !empty($user['accountId'])) {
                    try {
                        // Try fetching the user profile to get title
                        $profileResponse = Http::withBasicAuth(
                            config('services.jira.email'),
                            config('services.jira.api_token')
                        )->get("{$this->baseUrl}/rest/api/3/user/properties/jira.meta.user.job.title", [
                            'accountId' => $user['accountId']
                        ]);
                        
                        if ($profileResponse->successful()) {
                            $profileData = $profileResponse->json();
                            if (isset($profileData['value'])) {
                                $title = $profileData['value'];
                                if ($debug) {
                                    $this->info('Found title from user properties for ' . $user['displayName'] . ': ' . $title);
                                }
                            }
                        }
                        
                        // If we couldn't get it from properties, try manually creating title from name
                        if (empty($title)) {
                            // In Jira, sometimes the title is in the display name separated by a dash or comma
                            // Example: "John Doe - Developer" or "John Doe, Developer"
                            if (strpos($user['displayName'], ' - ') !== false) {
                                $parts = explode(' - ', $user['displayName']);
                                if (count($parts) > 1) {
                                    $title = trim($parts[1]);
                                    if ($debug) {
                                        $this->info('Extracted title from display name for ' . $user['displayName'] . ': ' . $title);
                                    }
                                }
                            } elseif (strpos($user['displayName'], ', ') !== false) {
                                $parts = explode(', ', $user['displayName']);
                                if (count($parts) > 1) {
                                    $title = trim($parts[1]);
                                    if ($debug) {
                                        $this->info('Extracted title from display name for ' . $user['displayName'] . ': ' . $title);
                                    }
                                }
                            }
                        }
                        
                    } catch (\Exception $e) {
                        if ($debug) {
                            $this->warn('Error fetching title for ' . $user['displayName'] . ': ' . $e->getMessage());
                        }
                    }
                }
                
                if ($debug) {
                    $this->info('Processing user: ' . $user['displayName'] . ' (' . $user['accountId'] . ') - Type: ' . $type . ' - Email: ' . $email);
                }
                
                // First find the existing employee to preserve the title if it exists
                $existingEmployee = Employee::where('external_id', $user['accountId'])
                    ->orWhere('email', $email)
                    ->first();

                $employeeData = [
                    'name' => $user['displayName'],
                    'email' => $email ?: $user['accountId'] . '@placeholder.com',
                    'avatar' => $avatarUrl,
                    'status' => 'active',
                    'type' => $type,
                    'external_id' => $user['accountId'],
                    'external_url' => $jiraService->getUserProfileUrl($user['accountId']),
                    'external_source' => 'jira',
                    'external_group' => $type === 'employee' ? 'jira-employees' : 'jira-external',
                ];

                // If we found a title, add it to the data
                if (!empty($title)) {
                    // Only update title if the existing employee doesn't have one
                    if (!$existingEmployee || empty($existingEmployee->title)) {
                        $employeeData['title'] = $title;
                    }
                }

                try {
                    if ($existingEmployee) {
                        // Update existing employee
                        $existingEmployee->update($employeeData);
                        if ($debug) {
                            $this->info('Updated existing employee: ' . $existingEmployee->name);
                        }
                    } else {
                        // Create new employee
                        Employee::create($employeeData);
                        if ($debug) {
                            $this->info('Created new employee: ' . $employeeData['name']);
                        }
                    }
                    
                    $syncedCount++;
                    if ($type === 'employee') {
                        $employeeCount++;
                    } else {
                        $externalCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error syncing user: ' . $user['displayName'], [
                        'error' => $e->getMessage(),
                        'user' => $user,
                        'data' => $employeeData
                    ]);
                    if ($debug) {
                        $this->error('Error syncing user ' . $user['displayName'] . ': ' . $e->getMessage());
                    }
                    continue;
                }
            }
            
            $this->info("Successfully synced $syncedCount users from Jira:");
            $this->info("- $employeeCount employees");
            $this->info("- $externalCount external resources");
            $this->info("- $skippedCount users skipped");
            
            // Mark any users not in the current sync as inactive
            $this->markOldUsersAsInactive();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error syncing Jira users: ' . $e->getMessage());
            Log::error('Error syncing Jira users', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
    
    /**
     * Mark users that were not updated in the current sync as inactive
     */
    protected function markOldUsersAsInactive()
    {
        $cutoffTime = now()->subMinutes(5);
        
        $count = Employee::where('external_source', 'jira')
            ->where('updated_at', '<', $cutoffTime)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);
        
        if ($count > 0) {
            $this->info("Marked $count users as inactive because they were not found in the current sync");
        }
    }
} 