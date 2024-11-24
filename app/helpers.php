<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

if (!function_exists('is_current_time_between')) {
    /**
     * Check if the current time is between the given start and end datetime.
     *
     * @param string $startDatetime
     * @param string $endDatetime
     * @return bool
     */
    function is_current_time_between($startDatetime, $endDatetime)
    {
        $currentTime = Carbon::now();
        $startDateTime = Carbon::parse($startDatetime);
        $endDateTime = Carbon::parse($endDatetime);

        return $currentTime->between($startDateTime, $endDateTime);
    }
}

if (!function_exists('update_env_value')) {
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

if (!function_exists('limit_str')) {
    /**
     * Limit the length of a string and append ellipses (...) if necessary.
     *
     * @param string $string The string to be limited.
     * @param int $limit The maximum length of the string.
     * @param string $end The string to append if the limit is exceeded (default: '...').
     * @return string The limited string.
     */
    function limit_str($string, $limit = 50, $end = '...')
    {
        if (mb_strlen($string) > $limit) {
            return mb_substr($string, 0, $limit) . $end;
        }

        return $string;
    }
}
