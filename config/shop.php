<?php

return [
    'opening_time' => '09:00',
    'closing_time' => '22:00',
    'appointment_min_duration_minutes' => (int) env('SHOP_APPOINTMENT_MIN_DURATION_MINUTES', 30),
    'appointment_max_duration_minutes' => (int) env('SHOP_APPOINTMENT_MAX_DURATION_MINUTES', 90),
    'max_active_pending_appointments' => (int) env('SHOP_MAX_ACTIVE_PENDING_APPOINTMENTS', 3),
];
