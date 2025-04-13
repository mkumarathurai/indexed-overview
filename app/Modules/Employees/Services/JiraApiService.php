<?php

namespace App\Modules\Employees\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JiraApiService
{
    protected $baseUrl;
    protected $apiToken;
    protected $email;
    protected $cacheTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->baseUrl = config('services.jira.base_url');
        $this->apiToken = config('services.jira.api_token');
        $this->email = config('services.jira.email');
    }

    /**
     * Get all users from Jira
     *
     * @param int $startAt Starting index for pagination
     * @param int $maxResults Maximum number of results to return
     * @return array
     */
    public function getUsers($startAt = 0, $maxResults = 50)
    {
        // Clear cache to ensure we get fresh data
        $cacheKey = "jira_users_{$startAt}_{$maxResults}";
        Cache::forget($cacheKey);
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($startAt, $maxResults) {
            try {
                Log::debug('Fetching users from Jira API', [
                    'url' => "{$this->baseUrl}/rest/api/3/users/search",
                    'email' => $this->email,
                    'startAt' => $startAt,
                    'maxResults' => $maxResults
                ]);
                
                // Jira Cloud API uses Basic Auth with email and token
                $response = Http::withBasicAuth($this->email, $this->apiToken)
                    ->get("{$this->baseUrl}/rest/api/3/users/search", [
                        'startAt' => $startAt,
                        'maxResults' => $maxResults
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    Log::debug('Successfully fetched users from Jira API', [
                        'count' => count($data), 
                        'first_user' => !empty($data) ? json_encode($data[0]) : 'none'
                    ]);
                    return $data;
                } else {
                    Log::error('Error fetching users from Jira API', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => "{$this->baseUrl}/rest/api/3/users/search"
                    ]);
                    return [];
                }
            } catch (\Exception $e) {
                Log::error('Exception when fetching users from Jira API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [];
            }
        });
    }

    /**
     * Get group ID by name
     *
     * @param string $groupName
     * @return string|null
     */
    public function getGroupId($groupName)
    {
        $cacheKey = "jira_group_id_{$groupName}";
        Cache::forget($cacheKey);
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($groupName) {
            try {
                Log::debug('Fetching group ID from Jira API', [
                    'url' => "{$this->baseUrl}/rest/api/3/group/bulk",
                    'groupName' => $groupName
                ]);
                
                $response = Http::withBasicAuth($this->email, $this->apiToken)
                    ->get("{$this->baseUrl}/rest/api/3/group/bulk", [
                        'groupName' => $groupName
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['groups']) && count($data['groups']) > 0) {
                        $groupId = $data['groups'][0]['groupId'] ?? null;
                        
                        Log::debug('Successfully fetched group ID from Jira API', [
                            'groupName' => $groupName,
                            'groupId' => $groupId
                        ]);
                        
                        return $groupId;
                    }
                    
                    Log::warning('Group not found in Jira', ['groupName' => $groupName]);
                    return null;
                } else {
                    Log::error('Error fetching group ID from Jira API', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'groupName' => $groupName
                    ]);
                    return null;
                }
            } catch (\Exception $e) {
                Log::error('Exception when fetching group ID from Jira API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'groupName' => $groupName
                ]);
                return null;
            }
        });
    }

    /**
     * Get users by group name
     *
     * @param string $groupName Group name to fetch users from
     * @return array
     */
    public function getUsersByGroup($groupName)
    {
        $cacheKey = "jira_users_by_group_{$groupName}";
        Cache::forget($cacheKey);
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($groupName) {
            try {
                $groupId = $this->getGroupId($groupName);
                
                if (!$groupId) {
                    Log::warning('Could not find group ID for group name', ['groupName' => $groupName]);
                    return [];
                }
                
                Log::debug('Fetching users by group from Jira API', [
                    'url' => "{$this->baseUrl}/rest/api/3/group/member",
                    'groupId' => $groupId,
                    'groupName' => $groupName
                ]);
                
                $response = Http::withBasicAuth($this->email, $this->apiToken)
                    ->get("{$this->baseUrl}/rest/api/3/group/member", [
                        'groupId' => $groupId,
                        'maxResults' => 100
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $users = $data['values'] ?? [];
                    
                    Log::debug('Successfully fetched users by group from Jira API', [
                        'groupName' => $groupName,
                        'count' => count($users)
                    ]);
                    
                    return $users;
                } else {
                    Log::error('Error fetching users by group from Jira API', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'groupName' => $groupName,
                        'groupId' => $groupId
                    ]);
                    return [];
                }
            } catch (\Exception $e) {
                Log::error('Exception when fetching users by group from Jira API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'groupName' => $groupName
                ]);
                return [];
            }
        });
    }

    /**
     * Get user profile URL
     *
     * @param string $accountId Jira account ID
     * @return string
     */
    public function getUserProfileUrl($accountId)
    {
        return "{$this->baseUrl}/jira/people/{$accountId}";
    }
    
    /**
     * Get user details including title
     *
     * @param string $accountId Jira account ID
     * @return array
     */
    public function getUserDetails($accountId)
    {
        $cacheKey = "jira_user_details_{$accountId}";
        Cache::forget($cacheKey);
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($accountId) {
            try {
                $userData = $this->fetchStandardUserDetails($accountId);
                
                // If we couldn't get basic user data, return empty array
                if (empty($userData)) {
                    return [];
                }
                
                // Try to fetch title information from people API
                $titleData = $this->fetchUserTitleFromPeopleApi($accountId);
                
                // If we found a title, add it to the user data
                if (!empty($titleData) && isset($titleData['title'])) {
                    $userData['title'] = $titleData['title'];
                    Log::debug('Found title for user', [
                        'accountId' => $accountId,
                        'title' => $titleData['title']
                    ]);
                }
                
                return $userData;
            } catch (\Exception $e) {
                Log::error('Exception when fetching user details from Jira API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'accountId' => $accountId
                ]);
                return [];
            }
        });
    }
    
    /**
     * Fetch standard user details from Jira user API
     *
     * @param string $accountId
     * @return array
     */
    private function fetchStandardUserDetails($accountId)
    {
        try {
            Log::debug('Fetching standard user details from Jira API', [
                'url' => "{$this->baseUrl}/rest/api/3/user",
                'accountId' => $accountId
            ]);
            
            $response = Http::withBasicAuth($this->email, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/user", [
                    'accountId' => $accountId,
                    'expand' => 'groups,applicationRoles,properties'
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::debug('Successfully fetched standard user details', [
                    'accountId' => $accountId
                ]);
                
                return $data;
            } else {
                Log::error('Error fetching standard user details', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Exception when fetching standard user details', [
                'message' => $e->getMessage()
            ]);
            
            return [];
        }
    }
    
    /**
     * Fetch user title from Jira People API
     *
     * @param string $accountId
     * @return array
     */
    private function fetchUserTitleFromPeopleApi($accountId)
    {
        try {
            // First try the user profile endpoint for title
            Log::debug('Fetching user title from Jira People API', [
                'accountId' => $accountId
            ]);
            
            // Try different API endpoints that might contain title information
            $profileEndpoints = [
                // People API (cloud)
                "{$this->baseUrl}/rest/api/3/user/properties/jira.meta.user.job.title?accountId={$accountId}",
                // User profile search API
                "{$this->baseUrl}/rest/api/3/user/viewissue/search?username={$accountId}",
                // Directory API
                "{$this->baseUrl}/rest/api/3/user/bulk?accountId={$accountId}&expand=properties"
            ];
            
            foreach ($profileEndpoints as $endpoint) {
                $response = Http::withBasicAuth($this->email, $this->apiToken)->get($endpoint);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Check different possible locations for title field
                    if (isset($data['value'])) {
                        // jira.meta.user.job.title format
                        return ['title' => $data['value']];
                    } elseif (is_array($data) && count($data) > 0 && isset($data[0]['title'])) {
                        // viewissue/search format
                        return ['title' => $data[0]['title']];
                    } elseif (isset($data['values']) && count($data['values']) > 0) {
                        // Bulk API format
                        foreach ($data['values'] as $user) {
                            if (isset($user['properties']) && isset($user['properties']['jira.meta.user.job.title'])) {
                                return ['title' => $user['properties']['jira.meta.user.job.title']['value']];
                            }
                        }
                    }
                    
                    Log::debug('Response from title endpoint did not contain expected title format', [
                        'endpoint' => $endpoint,
                        'response' => json_encode($data)
                    ]);
                } else {
                    Log::debug('Title endpoint request failed', [
                        'endpoint' => $endpoint,
                        'status' => $response->status()
                    ]);
                }
            }
            
            // If we reach here, we couldn't find a title in any endpoint
            return [];
        } catch (\Exception $e) {
            Log::error('Exception when fetching user title', [
                'message' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get issue details
     *
     * @param string $issueKey
     * @return array
     */
    public function getIssue($issueKey)
    {
        try {
            $response = Http::withBasicAuth($this->email, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/issue/{$issueKey}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error fetching issue details', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Exception when fetching issue details', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get worklogs for an issue
     *
     * @param string $issueKey
     * @return array
     */
    public function getIssueWorklogs($issueKey)
    {
        try {
            $response = Http::withBasicAuth($this->email, $this->apiToken)
                ->get("{$this->baseUrl}/rest/api/3/issue/{$issueKey}/worklog");

            if ($response->successful()) {
                return $response->json()['worklogs'] ?? [];
            }

            Log::error('Error fetching issue worklogs', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Exception when fetching issue worklogs', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getSubtasks(string $issueKey)
    {
        try {
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/3/issue/{$issueKey}", [
                'fields' => 'sub-tasks'
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch subtasks', [
                    'issue_key' => $issueKey,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return [];
            }

            $data = $response->json();
            return $data['fields']['sub-tasks'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch subtasks', [
                'issue_key' => $issueKey,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
} 