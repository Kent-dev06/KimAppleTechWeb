<?php

namespace App\Support;

class AppointmentTimeRules
{
    public static function errors(string $start, string $end): array
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $start) || ! preg_match('/^\d{2}:\d{2}$/', $end)) {
            return [];
        }

        $startMinutes = self::minutes($start);
        $endMinutes = self::minutes($end);
        $opening = self::minutes(config('shop.opening_time', '09:00'));
        $closing = self::minutes(config('shop.closing_time', '22:00'));
        $minimum = (int) config('shop.appointment_min_duration_minutes', 30);
        $maximum = (int) config('shop.appointment_max_duration_minutes', 120);
        $errors = [];

        if ($startMinutes < $opening || $startMinutes >= $closing) {
            $errors['start'] = 'Choose a start time during shop hours ('.config('shop.opening_time', '09:00').' to '.config('shop.closing_time', '22:00').').';
        }

        if ($endMinutes <= $opening || $endMinutes > $closing) {
            $errors['end'] = 'Choose an end time during shop hours ('.config('shop.opening_time', '09:00').' to '.config('shop.closing_time', '22:00').').';
        }

        if ($startMinutes % 30 !== 0) {
            $errors['start'] = 'Appointment times must use 30-minute intervals.';
        }

        if ($endMinutes % 30 !== 0) {
            $errors['end'] = 'Appointment times must use 30-minute intervals.';
        }

        $duration = $endMinutes - $startMinutes;
        $maximumHours = intdiv($maximum, 60);
        $maximumRemainder = $maximum % 60;
        $maximumLabel = $maximumHours > 0
            ? $maximumHours.' hour'.($maximumHours === 1 ? '' : 's').($maximumRemainder > 0 ? ' '.$maximumRemainder.' minutes' : '')
            : $maximum.' minutes';

        if ($duration <= 0) {
            $errors['end'] = 'The end time must be after the start time.';
        } elseif ($duration < $minimum || $duration > $maximum) {
            $errors['end'] = 'Appointments must be between '.$minimum.' minutes and '.$maximumLabel.'.';
        }

        return $errors;
    }

    public static function startLatest(): string
    {
        return self::format(self::minutes(config('shop.closing_time', '22:00')) - (int) config('shop.appointment_min_duration_minutes', 30));
    }

    public static function endEarliest(): string
    {
        return self::format(self::minutes(config('shop.opening_time', '09:00')) + (int) config('shop.appointment_min_duration_minutes', 30));
    }

    private static function minutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }

    private static function format(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
