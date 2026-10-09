<?php
namespace App\Http\Requests;
use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
class StoreAppointmentRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('access-customer-tools');}
 public function rules(): array{return ['customer_id'=>[Gate::allows('manage-repairs')?'required':'nullable','exists:customers,customer_id'],'device_id'=>['required','integer',Rule::exists('devices','device_id')->where('device_type','Smartphone')],'preferred_date'=>'required|date|after_or_equal:today','preferred_start_time'=>'required|date_format:H:i','preferred_end_time'=>'required|date_format:H:i|after:preferred_start_time','concern'=>'required|string|max:255'];}
 public function messages(): array{return ['preferred_date.after_or_equal'=>'Choose today or a future date.','preferred_end_time.after'=>'The end time must be after the start time.'];}
 public function withValidator($validator): void{$validator->after(function($validator){if($validator->errors()->isNotEmpty())return;$device=Device::find($this->integer('device_id'));if(!$device)return;$customerId=Gate::allows('manage-repairs')?$this->integer('customer_id'):$this->user()->customer?->customer_id;if(!$customerId||(int)$device->customer_id!==(int)$customerId)$validator->errors()->add('device_id','Choose a smartphone registered to the selected customer.');});}
}
