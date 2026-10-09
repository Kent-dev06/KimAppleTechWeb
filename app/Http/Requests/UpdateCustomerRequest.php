<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
class UpdateCustomerRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs')&&$this->user()->can('update',$this->route('customer'));}
 public function rules(): array{return ['name'=>'required|string|max:100','first_name'=>'required|string|max:50','last_name'=>'required|string|max:50','contact_number'=>'nullable|string|max:20','address'=>'nullable|string|max:255'];}
}
