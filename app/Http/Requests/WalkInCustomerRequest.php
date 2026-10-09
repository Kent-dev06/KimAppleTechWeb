<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
class WalkInCustomerRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs');}
 protected function prepareForValidation(): void{$this->merge(['first_name'=>Str::title(trim((string)$this->input('first_name'))),'last_name'=>Str::title(trim((string)$this->input('last_name')))]);}
 public function rules(): array{return ['email'=>'required|email|unique:users,email','first_name'=>'required|string|max:50','last_name'=>'required|string|max:50','contact_number'=>['required','regex:/^09[0-9]{9}$/'],'address'=>'nullable|string|max:255','password'=>'nullable|string|min:8'];}
}
