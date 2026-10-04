<?php

namespace App\Helpers;
use Carbon\Carbon;
use File;

class DateHelper
{

//    private function __construct()
//    {
//    }

    public static function getInstance()
    {
        return new self();
    }


    /**
     * @param $date
     * @return false|string
     */
    function customDateFormat($date)
    {
        return $date ? date('Y-m-d', strtotime($date)) : null;
    }

    /**
     * @param $date
     * @return false|string|null
     */
    function customDateFormatWithTime($date)
    {
        return $date ? date('d/m/Y H:i a', strtotime($date)) : null;
    }

    /**
     * @param $date
     * @return array
     */
    public function convertToTime($date)
    {
        $endTime = Carbon::parse($date);
        $currentTime = Carbon::now();

        if ($endTime > $currentTime) {
            $differenceInMilliseconds = $endTime->diffInMilliseconds($currentTime);
            $remainingTime = $endTime->diff($currentTime)->format('%H:%I:%S');
        } else {
            $differenceInMilliseconds = 0;
            $remainingTime = '00:00:00';
        }
        return [
            'differenceInMilliseconds' => $differenceInMilliseconds,
            'remainingTime' => $remainingTime,
        ];

    }


    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
    private function __construct()
    {
//        $this->excludedJulianDays =S $this->generateJulianDatesFor2025();
    }

    // Convert a given Gregorian date to Hijri date
    public function gregorianToHijri($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        if (!$date instanceof \DateTime) {
            throw new \InvalidArgumentException('The date must be a string or an instance of DateTime.');
        }

        // Get the Julian Day number for the Gregorian date
        $jd = gregoriantojd($date->format('m'), $date->format('d'), $date->format('Y'));

        // Convert Julian Day to Hijri date
        $l = $jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (int)((10985 - $l) / 5316) * (int)((50 * $l) / 17719) + (int)($l / 5670) * (int)((43 * $l) / 15238);
        $l = $l - (int)((30 - $j) / 15) * (int)((17719 * $j) / 50) - (int)($j / 16) * (int)((15238 * $j) / 43) + 29;

        // Calculate Hijri Month and Day from Julian Day
        $m = (int)((24 * $l) / 709);  // Hijri Month
        $d = $l - (int)((709 * $m) / 24);  // Hijri Day
        $y = 30 * $n + $j - 30;

        // Define the number of days in each Hijri month
        $monthDays = [
            1 => 30,  // Muharram
            2 => 29,  // Safar
            3 => 30,  // Rabi' al-Awwal
            4 => 29,  // Rabi' al-Thani
            5 => 30,  // Jumada al-Awwal
            6 => 29,  // Jumada al-Thani
            7 => 30,  // Rajab
            8 => 29,  // Sha'ban
            9 => 30,  // Ramadan
            10 => 30, // Shawwal
            11 => 29, // Dhu al-Qi'dah
            12 => 30  // Dhu al-Hijjah
        ];

        // Check if the day exceeds the number of days in the month, and adjust
        if ($d > $monthDays[$m]) {
            $d = 1;  // Reset day to 1
            $m++;    // Move to the next month
            if ($m > 12) {
                $m = 1; // Reset to the first month of the next Hijri year
                $y++;
            }
        }

        // If the current month should not have a 30th day (months like 29 days long)
        // Add 1 day to the date when it's an invalid transition for those months.
        if (($monthDays[$m] == 29 && $d == 30) || ($monthDays[$m] == 29 && $d > 30)) {
            // Add 1 day to the Hijri date to move to the next day
            $d = 1;
            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
        }

        // Finally, return the Hijri date in YYYY-MM-DD format
        return sprintf("%04d-%02d-%02d", $y, $m, $d);
    }

// Convert Julian Day to Hijri Month
    private function convertJulianToHijriMonth($jd)
    {
        $l = $jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (int)((10985 - $l) / 5316) * (int)((50 * $l) / 17719) + (int)($l / 5670) * (int)((43 * $l) / 15238);
        $l = $l - (int)((30 - $j) / 15) * (int)((17719 * $j) / 50) - (int)($j / 16) * (int)((15238 * $j) / 43) + 29;

        // Calculate Hijri Month from Julian Day
        $m = (int)((24 * $l) / 709);

        return $m;
    }

// Convert Julian Day to Hijri Day
    private function convertJulianToHijriDay($jd)
    {
        // Calculate the intermediate variable 'l'
        $l = $jd - 1948440 + 10632;
        $n = (int)(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (int)((10985 - $l) / 5316) * (int)((50 * $l) / 17719) + (int)($l / 5670) * (int)((43 * $l) / 15238);
        $l = $l - (int)((30 - $j) / 15) * (int)((17719 * $j) / 50) - (int)($j / 16) * (int)((15238 * $j) / 43) + 29;

        // Calculate Hijri Month
        $m = (int)((24 * $l) / 709);

        // Now calculate Hijri Day
        $d = $l - (int)((709 * $m) / 24);

        return $d;
    }

// Check if we need to add +1 Julian day (to adjust for month with no 30th day)
    private function shouldAddOneDay($month, $day)
    {
        // For example: months like Dhuʻl-Hijjah (12th month) do not have 30 days.
        // If we get 30 as the day for this month, we add 1 to the Julian Day.
        $monthsWithNo30th = [12];  // Dhuʻl-Hijjah (the 12th month) usually has 29 days

        // If the current month is in the list and the day is 30, we need to adjust
        if (in_array($month, $monthsWithNo30th) && $day == 30) {
            return true;
        }

        // Otherwise, no adjustment needed
        return false;
    }

}
