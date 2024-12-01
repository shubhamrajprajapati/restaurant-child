<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

if (! function_exists('is_current_time_between')) {
    /**
     * Check if the current time is between the given start and end datetime.
     *
     * @param  string  $startDatetime
     * @param  string  $endDatetime
     */
    function is_current_time_between($startDatetime, $endDatetime): bool
    {
        $currentTime = Carbon::now();

        // Ensure both startDatetime and endDatetime are Carbon instances
        $startDateTime = $startDatetime instanceof Carbon ? $startDatetime : Carbon::parse($startDatetime);
        $endDateTime = $endDatetime instanceof Carbon ? $endDatetime : Carbon::parse($endDatetime);

        return $currentTime->between($startDateTime, $endDateTime);
    }
}

if (! function_exists('update_env_value')) {
    function update_env_value($key, $value, $cache = true)
    {
        $envPath = base_path('.env');
        $oldValue = env($key); // Get the current value

        // Wrap the value in quotes if it contains spaces
        if (str_contains($value, ' ')) {
            $value = "\"{$value}\"";
        }

        if (file_exists($envPath)) {
            $fileContents = file_get_contents($envPath);
            $pattern = "/^{$key}=.*/m";

            // If the key exists in the .env file, update its value
            if (preg_match($pattern, $fileContents)) {
                $fileContents = preg_replace($pattern, "{$key}={$value}", $fileContents);
            } else {
                // If the key does not exist, append it
                $fileContents .= "\n{$key}={$value}\n";
            }

            file_put_contents($envPath, $fileContents);

            // Clear the config cache
            Artisan::call('config:clear');

            // Optionally rebuild the config cache
            if ($cache) {
                Artisan::call('config:cache');
            }

            return "Updated {$key} from '{$oldValue}' to '{$value}' successfully.";
        }

        return "Failed to update {$key}. .env file not found.";
    }
}

if (! function_exists('limit_str')) {
    /**
     * Limit the length of a string and append ellipses (...) if necessary.
     *
     * @param  string  $string  The string to be limited.
     * @param  int  $limit  The maximum length of the string.
     * @param  string  $end  The string to append if the limit is exceeded (default: '...').
     * @return string The limited string.
     */
    function limit_str($string, $limit = 50, $end = '...')
    {
        if (mb_strlen($string) > $limit) {
            return mb_substr($string, 0, $limit).$end;
        }

        return $string;
    }
}

if (! function_exists('invert_hex_color')) {
    /**
     * Invert a hex color code.
     *
     * @param  string|null  $hexColor  The hex color code (e.g., "#FFFFFF" or "FFFFFF").
     * @return string|null The inverted hex color code (e.g., "#000000"), or null if input is invalid.
     */
    function invert_hex_color(?string $hexColor): ?string
    {
        if (! $hexColor) {
            return null;
        }

        $hexColor = ltrim($hexColor, '#');

        // Validate the hex color (either 3 or 6 characters)
        if (! preg_match('/^[a-fA-F0-9]{3}$|^[a-fA-F0-9]{6}$/', $hexColor)) {
            return null; // Return null for invalid input instead of throwing an exception
        }

        // Expand shorthand hex (e.g., "ABC" -> "AABBCC")
        if (strlen($hexColor) === 3) {
            $hexColor = preg_replace('/(.)/', '$1$1', $hexColor);
        }

        // Invert the color
        $r = 255 - hexdec(substr($hexColor, 0, 2));
        $g = 255 - hexdec(substr($hexColor, 2, 2));
        $b = 255 - hexdec(substr($hexColor, 4, 2));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}

if (! function_exists('is_valid_hex_color')) {
    /**
     * Validate if a given value is a valid hex color.
     */
    function is_valid_hex_color(?string $hexColor): bool
    {
        return preg_match('/^#?[a-fA-F0-9]{3}$|^#?[a-fA-F0-9]{6}$/', $hexColor);
    }
}

if (!function_exists('generateTimeSlots')) {
    /**
     * Generate time slots between a start time and an end time with a given slot duration.
     *
     * @param string $startTimeStr Start time in "Y-m-d H:i" format.
     * @param Carbon|string $endTime End time as a Carbon instance or string.
     * @param int $slotDurationMinutes Duration of each slot in minutes.
     * @return array Array of slots with start and end times.
     * @throws InvalidArgumentException If end time is before start time.
     */
    function generateTimeSlots($startTime, $endTime, $slotDurationMinutes)
    {
        // Ensure that endTime is a Carbon instance
        if(!$startTime instanceof Carbon){
            $startTime = Carbon::parse($startTime);
        }

        // Ensure that endTime is a Carbon instance
        if (!$endTime instanceof Carbon) {
            $endTime = Carbon::parse($endTime);
        }

        // Ensure that endTime is after startTime
        if ($endTime <= $startTime) {
            // throw new InvalidArgumentException("End time must be after start time");
            return [];
        }

        // Generate time slots
        $slots = [];
        $currentTime = $startTime;

        while ($currentTime < $endTime) {
            $slotEndTime = $currentTime->copy()->addMinutes($slotDurationMinutes);
            if ($slotEndTime > $endTime) {
                break;
            }
            $slots[] = [
                'start' => $currentTime->format('h:i A'),
                'end' => $slotEndTime->format('h:i A')
            ];
            $currentTime = $slotEndTime; // Move to the next slot start time
        }

        return $slots;
    }
}

if (!function_exists('roundUpToNearestInterval')) {
    /**
     * Round up a time to the nearest interval.
     *
     * @param string $time The time to round (in H:i:s format).
     * @param int $interval The interval to round to (in minutes).
     * @return string The rounded time in H:i:s format.
     */
    function roundUpToNearestInterval($time, $interval)
    {
        // Convert time to total minutes from the start of the day
        $timestamp = strtotime($time);
        $hours = date('H', $timestamp);
        $minutes = date('i', $timestamp);

        $totalMinutes = $hours * 60 + $minutes;
        $intervalMinutes = $interval;

        // Calculate the rounded minutes
        $roundedMinutes = ceil($totalMinutes / $intervalMinutes) * $intervalMinutes;

        // Convert rounded minutes back to hours and minutes
        $roundedHours = floor($roundedMinutes / 60);
        $roundedMinutes = $roundedMinutes % 60;

        // Format the result as H:i:s
        return sprintf('%02d:%02d:%02d', $roundedHours, $roundedMinutes, 0);
    }
}

if (!function_exists('getDatesBetween')) {
    /**
     * Returns an array of dates between two DateTime objects, inclusive.
     *
     * @param DateTime|string $startDate Start date as a DateTime object or string in 'Y-m-d' format.
     * @param DateTime|string $endDate End date as a DateTime object or string in 'Y-m-d' format.
     * @return array An array of dates in 'Y-m-d' format, or an empty array if start date is after end date.
     */
    function getDatesBetween($startDate, $endDate)
    {
        // Convert to DateTime if they are strings
        if (is_string($startDate)) {
            $startDate = new DateTime($startDate);
        }
        if (is_string($endDate)) {
            $endDate = new DateTime($endDate);
        }

        // Check if input is valid DateTime objects
        if (!($startDate instanceof DateTime) || !($endDate instanceof DateTime)) {
            return [];
        }

        // Ensure $endDate is inclusive
        $endDate->modify('+1 day');

        // If start date is after end date, return empty array
        if ($startDate > $endDate) {
            return [];
        }

        // Initialize the array to hold dates
        $dates = [];

        // Create the DatePeriod
        $interval = new DateInterval('P1D'); // 1 day interval
        $period = new DatePeriod($startDate, $interval, $endDate);

        // Populate the dates array
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}


