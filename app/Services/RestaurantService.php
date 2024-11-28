<?php

namespace App\Services;

use App\CentralLogics\Helpers;
use App\Models\Restaurant;
use App\Models\RollingMessage;

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

    public function getRollingMessage(): ?string
    {
        $rollingMsgData = RollingMessage::first();

        if ($rollingMsgData) {
            // Check for holiday message
            if ($this->isValidHoliday($rollingMsgData)) {
                return $rollingMsgData?->holiday_marquee;
            }

            // Check for regular message
            if ($rollingMsgData?->marquee_status && $rollingMsgData?->active_marquee_no > 0) {
                $marqueeColName = "marquee_{$rollingMsgData->active_marquee_no}";
                return $rollingMsgData?->$marqueeColName;
            }
        }

        return null;
    }

    public function getTestimonialsData(array $data = []): ?array
    {
        $testimonialData = !empty($data['testimonials']) && !empty($data['testimonials'][0]) && !empty($data['testimonials'][0]['status']) && !empty($data['testimonials'][0]['reviews']) && count($data['testimonials'][0]['reviews']);

        if ($testimonialData) {
            // return dd($data['testimonials'][0]['reviews']);
            return $data['testimonials'][0]['reviews'];
        } else {
            return [];
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

    private function isValidHoliday(RollingMessage $data): bool
    {
        $holidayStatus = $data?->holiday_marquee_status;
        $startDatetime = $data?->start_datetime;
        $endDatetime = $data?->end_datetime;

        return $holidayStatus && $startDatetime && $endDatetime &&
        is_current_time_between($startDatetime, $endDatetime);
    }

    public function getMetaDataDetails(array $data): ?array
    {
        $data = !empty($data['meta_details']) ? $data['meta_details'][0] : [];
        return [
            "main_page_status" => $data['main_page_status'] ?? false,
            "main_page_title" => $data['main_page_title'] ?? null,
            "main_page_description" => $data['main_page_description'] ?? null,
            "main_page_keywords" => $data['main_page_keywords'] ?? "",

            "reviews_page_status" => $data['reviews_page_status'] ?? false,
            "reviews_page_title" => $data['reviews_page_title'] ?? null,
            "reviews_page_description" => $data['reviews_page_description'] ?? null,
            "reviews_page_keywords" => $data['reviews_page_keywords'] ?? "",

            "reservation_page_status" => $data['reservation_page_status'] ?? false,
            "reservation_page_title" => $data['reservation_page_title'] ?? null,
            "reservation_page_description" => $data['reservation_page_description'] ?? null,
            "reservation_page_keywords" => $data['reservation_page_keywords'] ?? "",

            "restaurant_menu_page_status" => $data['restaurant_menu_page_status'] ?? false,
            "restaurant_menu_page_title" => $data['restaurant_menu_page_title'] ?? null,
            "restaurant_menu_page_description" => $data['restaurant_menu_page_description'] ?? null,
            "restaurant_menu_page_keywords" => $data['restaurant_menu_page_keywords'] ?? "",

            "takeaway_menu_page_status" => $data['takeaway_menu_page_status'] ?? false,
            "takeaway_menu_page_keywords" => $data['takeaway_menu_page_keywords'] ?? "",
            "takeaway_menu_page_title" => $data['takeaway_menu_page_title'] ?? null,
            "takeaway_menu_page_description" => $data['takeaway_menu_page_description'] ?? null,

            "order_online_page_status" => $data['order_online_page_status'] ?? false,
            "order_online_page_keywords" => $data['order_online_page_keywords'] ?? "",
            "order_online_page_title" => $data['order_online_page_title'] ?? null,
            "order_online_page_description" => $data['order_online_page_description'] ?? null,
        ];
    }

    public function getSocialMediaDetails(array $data): ?array
    {
        return [
            "instagram_link_status" => $data['instagram_link_status'] ?? false,
            "instagram_link" => $data['instagram_link'] ?? null,

            "facebook_link_status" => $data['facebook_link_status'] ?? false,
            "facebook_link" => $data['facebook_link'] ?? null,

            "tripadvisor_link_status" => $data['tripadvisor_link_status'] ?? false,
            "tripadvisor_link" => $data['tripadvisor_link'] ?? null,

            "whatsapp_link_status" => $data['whatsapp_link_status'] ?? false,
            "whatsapp_link" => $data['whatsapp_link'] ?? null,

            "youtube_link_status" => $data['youtube_link_status'] ?? false,
            "youtube_link" => $data['youtube_link'] ?? null,

            "google_review_link_status" => $data['google_review_link_status'] ?? false,
            "google_review_link" => $data['google_review_link'] ?? null,

            "custom_link_1_status" => $data['custom_link_1_status'] ?? false,
            "custom_link_1_url" => $data['custom_link_1_url'] ?? null,
            "custom_link_1_img" => Helpers::get_img_full_url('custom_social_links', $data['custom_link_1_img'] ?? null, 'public'),

            "custom_link_2_status" => $data['custom_link_2_status'] ?? false,
            "custom_link_2_url" => $data['custom_link_2_url'] ?? null,
            "custom_link_2_img" => Helpers::get_img_full_url('custom_social_links', $data['custom_link_2_img'] ?? null, 'public'),

            "custom_link_3_status" => $data['custom_link_3_status'] ?? false,
            "custom_link_3_url" => $data['custom_link_3_url'] ?? null,
            "custom_link_3_img" => Helpers::get_img_full_url('custom_social_links', $data['custom_link_3_img'] ?? null, 'public'),

            "custom_link_4_status" => $data['custom_link_4_status'] ?? false,
            "custom_link_4_url" => $data['custom_link_4_url'] ?? null,
            "custom_link_4_img" => Helpers::get_img_full_url('custom_social_links', $data['custom_link_4_img'] ?? null, 'public'),

            "custom_link_5_status" => $data['custom_link_5_status'] ?? "0",
            "custom_link_5_img" => $data['custom_link_5_img'] ?? null,
            "custom_link_5_url" => Helpers::get_img_full_url('custom_social_links', $data['custom_link_5_url'] ?? null, 'public'),
        ];
    }
}
