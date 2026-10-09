<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProfileRequest extends FormRequest {
 public function authorize(): bool{return (bool) ($this->user() && $this->user()->can('update-own-profile') && $this->user()->can('update',$this->user()->customer));}
 public function rules(): array{return ['first_name'=>'required|string|max:50','last_name'=>'required|string|max:50','contact_number'=>'nullable|string|max:20','address'=>'nullable|string|max:255'];}
}
