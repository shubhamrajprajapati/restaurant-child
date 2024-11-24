<?php

namespace App\Services;

use App\Models\Restaurant;

class RestaurantService
{
    public function getApiData($key = 'all')
    {
        $apiData = app('api.data');
        return $key == 'all' ? $apiData : $apiData[$key] ?? null;
    }
    public function getRestaurantData($domain = null): ?array
    {
        $domain ??= $this->getApiData('domain');
        $data = Restaurant::where('domain', $domain)->first()?->toArray();

        // Safely decode JSON if other_details is present and not already an array
        $data['other_details'] = isset($data['other_details']) && !is_array($data['other_details'])
        ? json_decode($data['other_details'], true)
        : $data['other_details'] ?? null;

        return $data;
    }

    public function getRollingMessage(array $data = null): ?string
    {
        $rollingData = $data['rolling_messages'][0] ?? null;

        if ($rollingData) {
            // Check for holiday message
            if ($this->isValidHoliday($rollingData)) {
                return $rollingData['holiday_msg'] ?? null;
            }

            // Check for regular message
            if (!empty($rollingData['regular_status'])) {
                return $rollingData['regular_active_msg'] ?? null;
            }
        }

        return null;
    }

    public function getTestimonialsData(array $data = []): ?array
    {
        $testimonialData = !empty($data['testimonials']) && !empty($data['testimonials'][0]) && !empty($data['testimonials'][0]['status']) && !empty($data['testimonials'][0]['reviews']) && count($data['testimonials'][0]['reviews']);

        if ($testimonialData) {
            return $data[0]['reviews'];
        } else {
            return [
                [
                    'name' => 'John Doe',
                    'review' => 'Awesome! you can change this testimonial in the admin panel.',
                ],
                [
                    'name' => 'Lorem',
                    'review' => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Ullam, facilis.",
                ],
                [
                    'name' => 'Ratan Raj',
                    'review' => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti dignissimos ipsa reiciendis magni ea praesentium.",
                ],
                [
                    'name' => 'Octell',
                    'review' => "tempore, quasi iste consectetur harum quis, minus praesentium excepturi quia explicabo quisquam maxime dolor",
                ],
            ];
        }
    }

    public function checkIsRestaurantClose(array $apiData, array $restaurantData)
    {
        $appName = $restaurantData['name'] ?? $apiData['name'] ?? config('app.name');

        $statusApi = $apiData['status'] ?? false;
        $statusRestaurant = $restaurantData['status'] ?? false;

        $statusMsgApi = $apiData['status_msg'] ?? '';
        $statusMsgRestaurant = $restaurantData['status_msg'] ?? '';

        return [
            'name' => $appName,
            'status' => $statusApi || $statusRestaurant,
            'message' => $statusApi ? $statusMsgApi : $statusMsgRestaurant,
        ];
    }

    private function isValidHoliday(array $data): bool
    {
        // Safely access required keys with default values
        $holidayStatus = !empty($data['holiday_status']);
        $startDate = $data['holiday_start_date'] ?? null;
        $endDate = $data['holiday_end_date'] ?? null;

        return !empty($data['holiday_msg']) &&
        $holidayStatus &&
        $startDate &&
        $endDate &&
        is_current_time_between($startDate, $endDate);
    }
}
