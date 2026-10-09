<?php
namespace App\Http\Requests;
use App\Models\Appointment;
use App\Support\AppointmentTimeRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
class ConfirmAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-repairs');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Confirmed', 'Rescheduled', 'Cancelled', 'Completed', 'No-show'])],
            'confirmed_date' => ['nullable', 'required_if:status,Confirmed,Rescheduled', 'date', 'after_or_equal:today'],
            'confirmed_start_time' => ['nullable', 'required_if:status,Confirmed,Rescheduled', 'date_format:H:i', 'after_or_equal:'.config('shop.opening_time')],
            'confirmed_end_time' => ['nullable', 'required_if:status,Confirmed,Rescheduled', 'date_format:H:i', 'after:confirmed_start_time', 'before_or_equal:'.config('shop.closing_time')],
            'appointment_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmed_date.after_or_equal' => 'Choose today or a future confirmed date.',
            'confirmed_start_time.after_or_equal' => 'Appointments can start from '.config('shop.opening_time').'.',
            'confirmed_end_time.after' => 'The end time must be after the start time.',
            'confirmed_end_time.before_or_equal' => 'Appointments must end by '.config('shop.closing_time').'.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $values = $this->all();
            $appointment = $this->route('appointment');

            if ($appointment->device->device_type !== 'Smartphone') {
                $validator->errors()->add('status', 'Only smartphone repair appointments can be managed here.');

                return;
            }

            if (($values['status'] ?? null) === 'Completed') {
                $appointmentDate = $appointment->confirmed_date ?? $appointment->preferred_date;

                if ($appointmentDate?->isAfter(today())) {
                    $validator->errors()->add('status', 'This appointment cannot be completed before its scheduled date.');
                }
            }

            if (($values['status'] ?? null) === 'No-show' && $appointment->repairRecord()->exists()) {
                $validator->errors()->add('status', 'An appointment with an existing repair record cannot be marked No-show.');

                return;
            }

            if (! in_array($values['status'] ?? null, ['Confirmed', 'Rescheduled'], true)
                || empty($values['confirmed_start_time'])
                || empty($values['confirmed_end_time'])) {
                return;
            }

            foreach (AppointmentTimeRules::errors($values['confirmed_start_time'], $values['confirmed_end_time']) as $field => $message) {
                $validator->errors()->add('confirmed_'.$field.'_time', $message);
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $slots = Appointment::whereIn('status', ['Confirmed', 'Rescheduled'])
                ->whereDate('confirmed_date', $values['confirmed_date'])
                ->where('appointment_id', '!=', $appointment->appointment_id)
                ->get(['confirmed_start_time', 'confirmed_end_time']);
            $start = substr($values['confirmed_start_time'], 0, 5);
            $end = substr($values['confirmed_end_time'], 0, 5);

            if ($slots->contains(fn ($slot) => $start < substr((string) $slot->confirmed_end_time, 0, 5)
                && $end > substr((string) $slot->confirmed_start_time, 0, 5))) {
                $validator->errors()->add('confirmed_start_time', 'That time overlaps an already confirmed appointment.');
            }
        });
    }
}
