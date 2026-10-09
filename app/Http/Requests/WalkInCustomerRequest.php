<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
class WalkInCustomerRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs');}
 public function rules(): array{return ['email'=>'required|email|unique:users,email','first_name'=>'required|string|max:50','last_name'=>'required|string|max:50','contact_number'=>['required','regex:/^09[0-9]{9}$/'],'address'=>'nullable|string|max:255','password'=>'nullable|string|min:8'];}
}
