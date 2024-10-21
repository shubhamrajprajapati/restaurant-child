<?php

namespace App\Providers;

use App\Exceptions\ApiDataException;
use App\Services\SuperAdminApiService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Bind your service to the container
        $this->app->singleton(SuperAdminApiService::class, function () {
            return new SuperAdminApiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Instantiate your service
        $apiService = App::make(SuperAdminApiService::class);

        // Perform the API request
        $data = $apiService->requestData();

        // Check if data is null
        if (is_null($data)) {
            // Throw your custom exception
            throw new ApiDataException('Failed to retrieve data. Application cannot continue.');
        }

        // Bind the data to the service container
        $this->app->singleton('api.data', function () use ($data) {
            return $data;
        });
    }
}
