<?php
namespace App\Http\Requests;
use App\Models\{Appointment,Device};
use App\Support\AppointmentTimeRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('access-customer-tools');
    }

    public function rules(): array
    {
        return [
            'customer_id' => [Gate::allows('manage-repairs') ? 'required' : 'nullable', 'exists:customers,customer_id'],
            'device_id' => ['required', 'integer', Rule::exists('devices', 'device_id')->where('device_type', 'Smartphone')],
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_start_time' => ['required', 'date_format:H:i', 'after_or_equal:'.config('shop.opening_time')],
            'preferred_end_time' => ['required', 'date_format:H:i', 'after:preferred_start_time', 'before_or_equal:'.config('shop.closing_time')],
            'concern' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'preferred_date.after_or_equal' => 'Choose today or a future date.',
            'preferred_start_time.after_or_equal' => 'Appointments can start from '.config('shop.opening_time').'.',
            'preferred_end_time.after' => 'The end time must be after the start time.',
            'preferred_end_time.before_or_equal' => 'Appointments must end by '.config('shop.closing_time').'.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $start = $this->input('preferred_start_time');
            $end = $this->input('preferred_end_time');

            if (is_string($start) && is_string($end)) {
                foreach (AppointmentTimeRules::errors($start, $end) as $field => $message) {
                    $validator->errors()->add('preferred_'.$field.'_time', $message);
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $device = Device::find($this->integer('device_id'));

            if (! $device) {
                return;
            }

            $customerId = Gate::allows('manage-repairs')
                ? $this->integer('customer_id')
                : $this->user()->customer?->customer_id;

            if (! $customerId || (int) $device->customer_id !== (int) $customerId) {
                $validator->errors()->add('device_id', 'Choose a smartphone registered to the selected customer.');

                return;
            }

            $pendingLimit = (int) config('shop.max_active_pending_appointments', 3);
            $activePending = Appointment::where('customer_id', $customerId)->where('status', 'Pending')->count();

            if ($activePending >= $pendingLimit) {
                $errorField = Gate::allows('manage-repairs') ? 'customer_id' : 'device_id';
                $message = Gate::allows('manage-repairs')
                    ? 'This customer already has the maximum of '.$pendingLimit.' active pending appointments.'
                    : 'You can have up to '.$pendingLimit.' active pending appointments at a time.';
                $validator->errors()->add($errorField, $message);
            }
        });
    }
}
