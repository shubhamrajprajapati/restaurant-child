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
        $this->baseUrl = config('app.api_url');
        $this->appUrl = config('app.restaurant_url');
    }

    public function requestData()
    {
        $response = Http::get("{$this->baseUrl}/api/v1/settings/{$this->appUrl}");
        return $response->json();
    }
}
