<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    /**
     * Convert restaurant local time to UTC.
     */
    public static function toUtc(string $date, string $time, string $timezone): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', "$date $time", $timezone)->utc();
    }

    /**
     * Convert UTC to restaurant local time.
     */
    public static function fromUtc(Carbon $utc, string $timezone): string
    {
        return $utc->copy()->tz($timezone)->format('H:i');
    }

    /**
     * Format date for display in restaurant timezone.
     */
    public static function formatDate(string $date, string $timezone, string $format = 'Y-m-d'): string
    {
        return Carbon::parse($date)->tz($timezone)->format($format);
    }

    /**
     * Format time for display in restaurant timezone.
     */
    public static function formatTime(string $time, string $timezone): string
    {
        return Carbon::createFromFormat('H:i', $time, $timezone)->format('H:i');
    }
}