<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JiraClient
{
    private $apiToken;
    private $baseUrl;

    public function __construct(string $apiToken, string $baseUrl)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path, array $query = [])
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        
        try {
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                $this->apiToken
            )->get($url, $query);

            $response->throw();

            return $response;
        } catch (\Exception $e) {
            Log::error('Jira API request failed', [
                'url' => $url,
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 