<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SuperAdminApiService
{
    protected $baseUrl;
    protected $appUrl;

    public function __construct()
    {
        $this->baseUrl = env('API_URL'); // Load from .env
        $this->appUrl = env('RESTAURANT_URL', config('app.url')); // Load from env otherwise config
    }

    public function requestData()
    {
        $response = Http::get("{$this->baseUrl}/api/v1/settings/{$this->appUrl}");
        return $response->json();
    }
}
