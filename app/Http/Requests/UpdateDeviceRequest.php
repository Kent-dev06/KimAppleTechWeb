<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
class UpdateDeviceRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs')&&$this->user()->can('manage',$this->route('device'));}
 public function rules(): array{return ['brand'=>'required|string|max:50','model'=>'required|string|max:50','serial_number'=>'nullable|string|max:50'];}
}
