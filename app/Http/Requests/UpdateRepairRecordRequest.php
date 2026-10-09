<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
class UpdateRepairRecordRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs')&&$this->user()->can('manage',$this->route('repair'));}
 public function rules(): array{return ['diagnosis'=>'nullable|string','parts_used'=>'nullable|string','technician_notes'=>'nullable|string','repair_status'=>['required',Rule::in(['Pending','Received','Diagnosing','In Repair','Ready for Pickup','Completed'])],'cost_estimate'=>'required|numeric|min:0','date_completed'=>'nullable|date'];}
}
