<?php

namespace App\Http\Controllers;

use App\Models\ColorTheme;
use App\Services\PageEditService;
use App\Services\RestaurantService;

class RestaurantController extends Controller
{
    protected $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    public function index()
    {
        $apiData = $this->restaurantService->getApiData();
        $data = $this->restaurantService->getRestaurantData();

        $restaurantCloseInfo = $this->restaurantService->checkIsRestaurantClose($apiData, $data);

        if ($restaurantCloseInfo->status) {
            return view('layouts.partials.close-restaurant', ['data' => $restaurantCloseInfo]);
        }

        return view('home', compact('apiData', 'data'));
    }
}
