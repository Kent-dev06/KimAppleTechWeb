<?php
namespace App\Http\Requests;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
class StoreRepairRecordRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs');}
 public function rules(): array{return ['appointment_id'=>'required|exists:appointments,appointment_id','diagnosis'=>'nullable|string','parts_used'=>'nullable|string','technician_notes'=>'nullable|string','repair_status'=>['required',Rule::in(['Pending','Received','Diagnosing','In Repair','Ready for Pickup','Completed'])],'cost_estimate'=>'required|numeric|min:0','date_completed'=>'nullable|date'];}
 public function withValidator($validator): void{$validator->after(function($validator){if($validator->errors()->isNotEmpty())return;$appointment=Appointment::with('device')->find($this->integer('appointment_id'));if($appointment&&!in_array($appointment->status,['Pending','Confirmed','Rescheduled','Completed'],true))$validator->errors()->add('appointment_id','A repair can only be recorded for an active appointment.');});}
}
