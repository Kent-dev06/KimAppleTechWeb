<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
class UpdateCustomerRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs')&&$this->user()->can('update',$this->route('customer'));}
 protected function prepareForValidation(): void{$this->merge(['first_name'=>Str::title(trim((string)$this->input('first_name'))),'last_name'=>Str::title(trim((string)$this->input('last_name')))]);}
 public function rules(): array{return ['first_name'=>'required|string|max:50','last_name'=>'required|string|max:50','contact_number'=>'nullable|string|max:20','address'=>'nullable|string|max:255'];}
}
