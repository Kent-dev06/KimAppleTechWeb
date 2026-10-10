<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
class StoreDeviceRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('access-customer-tools');}
 public function rules(): array{return ['customer_id'=>[Gate::allows('manage-repairs')?'required':'nullable','exists:customers,customer_id'],'device_type'=>'required|in:Smartphone,Computer','brand'=>'required|string|max:50','model'=>'required|string|max:50','serial_number'=>'nullable|string|max:50'];}
}
