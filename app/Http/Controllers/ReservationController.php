<?php

namespace App\Http\Controllers;

use App\Models\ColorTheme;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\RollingMessage;
use App\Services\PageEditService;
use App\Services\RestaurantService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ReservationController extends Controller
{
    public function index()
    {
        $superAdminApiData = Cache::get('super_admin_api_data');
        $reservationSetting = Cache::get('reservation_setting');
        $openingHour = OpeningHour::whereActive(true)->first();

        if ($superAdminApiData?->reservation_status && $reservationSetting && $openingHour) {
            $openingHourSlots = [];
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            for ($day = 0; $day <= 6; $day++) {
                $lowerDay = strtolower($weekdays[$day]);

                // Construct dynamic keys for time slots
                $stime1Key = "{$lowerDay}_start_time_1";
                $etime1Key = "{$lowerDay}_end_time_1";
                $stime2Key = "{$lowerDay}_start_time_2";
                $etime2Key = "{$lowerDay}_end_time_2";

                $openingHourSlots[$day] = [
                    'slot1' => generateTimeSlots($openingHour->$stime1Key, $openingHour->$etime1Key, $reservationSetting->time_interval),
                    'slot2' => generateTimeSlots($openingHour->$stime2Key, $openingHour->$etime2Key, $reservationSetting->time_interval),
                ];
            }

            // Generate time slots for today
            $currentWeekDay = strtolower(date('l'));
            $todayStime1Key = "{$currentWeekDay}_start_time_1";
            $todayEtime1Key = "{$currentWeekDay}_end_time_1";
            $todayStime2Key = "{$currentWeekDay}_start_time_2";
            $todayEtime2Key = "{$currentWeekDay}_end_time_2";

            $currentTime = date('H:i:s'); // Get the current time
            $startTime1Today = roundUpToNearestInterval($currentTime, $reservationSetting->time_interval);
            $startTime2Today = roundUpToNearestInterval($currentTime, $reservationSetting->time_interval);

            $openingHourSlots['today'] = [
                'slot1' => generateTimeSlots($startTime1Today, $openingHour->$todayEtime1Key, $reservationSetting->time_interval),
                'slot2' => generateTimeSlots($startTime2Today, $openingHour->$todayEtime2Key, $reservationSetting->time_interval),
            ];

            $disabledWeekdays = [
                'days' => [],
                'reasons' => [],
            ];
            $disabledDates = [
                'days' => [],
                'reasons' => [],
            ];

            // Create closing hours weedays
            if ($openingHour) {
                for ($i = 0; $i <= 6; $i++) {
                    $lowerDay = strtolower($weekdays[$i]);
                    $open = "{$lowerDay}_open";
                    if (!$openingHour->$open) {
                        $disabledWeekdays['days'][] = $i; // Store day index as value
                        $disabledWeekdays['reasons'][$i] = "Closed"; // Store reason with corresponding index
                    }
                }
            }

            $holidayDays = RollingMessage::whereHolidayMarqueeStatus(true)->first();

            if ($holidayDays) {
                // Get holiday list between two days
                $holidayDateLists = getDatesBetween($holidayDays->holiday_marquee_start_date, $holidayDays->holiday_marquee_end_date);
                foreach ($holidayDateLists as $date) {
                    $disabledDates['days'][] = $date;
                    $disabledDates['reasons'][$date] = $holidayDays->holiday_marquee;
                }
            }

            // Convert to collection
            $disabledWeekdays = collect($disabledWeekdays);
            $disabledDates = collect($disabledDates);
            $openingHourSlots = collect($openingHourSlots);

            $isOpen = true;
            $close_msg = '';

            // Render the view
            return view('frontend.reservation', compact('isOpen', 'close_msg', 'disabledWeekdays', 'disabledDates', 'openingHourSlots'));
        } else {
            $isOpen = false;
            $defaultMessage = 'Sorry, Reservation is closed.';
            $close_msg = !$superAdminApiData?->reservation_status
            ? ($superAdminApiData?->reservation_msg ?? $defaultMessage)
            : ($reservationSetting?->close_msg ?? $defaultMessage);

            // Render the view
            return view('frontend.reservation', compact('isOpen', 'close_msg'));
        }

    }

    public function create(Request $request)
    {
        // Extract and process request data
        $bookingData = $this->processCreateNewReservationtData($request->request->all());

        // Backend Validation
        foreach ($bookingData as $key => $value) {
            if ($key == 'spinst') {
                continue;
            }
            // Skip the 'spinst' field

            if ($this->isFieldEmpty($value)) {
                // Prepare a safer error message without exposing sensitive data
                $errorMessage = "Please fill all the fields.";

                // Optionally log the booking data for debugging
                // error_log(json_encode($bookingData));

                return json_encode([
                    "status" => "danger",
                    "title" => "All Fields Required",
                    "message" => $errorMessage,
                ]);
            }
        }

        // Update the database
        try {
            // Convert to 24-hour format
            $bookingData['time'] = Carbon::createFromFormat('h:i A', $bookingData['time'])->format('H:i:s');

            // Create a new reservation
            $reservation = Reservation::create($bookingData);

            if ($reservation) {
                return json_encode([
                    "status" => "success",
                    "title" => "Wooho!, Reserved.",
                    "message" => "Your reservation is booked successfully. You'll receive email from our website.",
                ]);
            } else {
                return json_encode([
                    "status" => "error",
                    "title" => "Can't Reserve Now",
                    "message" => "Some error occurred. Please try again later.",
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                "status" => "danger",
                "title" => "Can't Reserve Now",
                "message" => "Some error occurred. Please try again later. $e",
            ]);
        }
    }

    private function processCreateNewReservationtData(array $requestData)
    {
        return [
            'name' => $requestData['fullname'],
            'phone' => $requestData['phone'],
            'email' => $requestData['email'],
            'date' => $requestData['resdate'],
            'time' => $requestData['restime'],
            'persons' => $requestData['persons'],
            'comments' => $requestData['spinst'],
        ];
    }

    /**
     * Check if the value is empty
     */
    private function isFieldEmpty($field)
    {
        return empty($field) && $field !== '0';
    }
}
