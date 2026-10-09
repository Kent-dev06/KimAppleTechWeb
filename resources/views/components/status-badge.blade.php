@props(['status'])
@php
    $variant = match ($status) {
        'Pending' => 'warning',
        'Confirmed', 'In Repair' => 'primary',
        'Completed' => 'success',
        'Cancelled', 'No-show' => 'danger',
        'Rescheduled', 'Received', 'Ready for Pickup' => 'info',
        'Diagnosing' => 'secondary',
        default => 'secondary',
    };
@endphp
<span {{ $attributes->class(['badge', 'text-bg-'.$variant]) }}>{{ $status }}</span>
