<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    public static function toUtc(string $date, string $time, string $timezone): Carbon
    {
        $time = substr($time, 0, 5);
        $date = substr($date, 0, 10);

        return Carbon::createFromFormat('Y-m-d H:i', "$date $time", $timezone)->utc();
    }

    public static function fromUtc(Carbon $utc, string $timezone): string
    {
        return $utc->copy()->tz($timezone)->format('H:i');
    }

    public static function formatDate(string $date, string $timezone, string $format = 'Y-m-d'): string
    {
        return Carbon::parse($date)->tz($timezone)->format($format);
    }

    public static function formatTime(string $time, string $timezone): string
    {
        $time = substr($time, 0, 5);

        return Carbon::createFromFormat('H:i', $time, $timezone)->format('H:i');
    }
}
