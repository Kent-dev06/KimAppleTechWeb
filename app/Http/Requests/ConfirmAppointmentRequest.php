<?php
namespace App\Http\Requests;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
class ConfirmAppointmentRequest extends FormRequest {
 public function authorize(): bool{return Gate::allows('manage-repairs');}
 public function rules(): array{return ['status'=>['required',Rule::in(['Confirmed','Rescheduled','Cancelled','Completed','No-show'])],'confirmed_date'=>'nullable|required_if:status,Confirmed,Rescheduled|date','confirmed_start_time'=>'nullable|required_if:status,Confirmed,Rescheduled|date_format:H:i','confirmed_end_time'=>'nullable|required_if:status,Confirmed,Rescheduled|date_format:H:i|after:confirmed_start_time'];}
 public function withValidator($validator): void{$validator->after(function($validator){$v=$this->all();if($validator->errors()->isNotEmpty())return;$appointment=$this->route('appointment');if($appointment->device->device_type!=='Smartphone'){$validator->errors()->add('status','Only smartphone repair appointments can be managed here.');return;}if(($v['status']??null)==='No-show'&&$appointment->repairRecord()->exists()){$validator->errors()->add('status','An appointment with an existing repair record cannot be marked No-show.');return;}if(!in_array($v['status']??null,['Confirmed','Rescheduled'],true)||empty($v['confirmed_date'])||empty($v['confirmed_start_time'])||empty($v['confirmed_end_time']))return;$slots=Appointment::whereIn('status',['Confirmed','Rescheduled'])->whereDate('confirmed_date',$v['confirmed_date'])->where('appointment_id','!=',$appointment->appointment_id)->get(['confirmed_start_time','confirmed_end_time']);$start=substr($v['confirmed_start_time'],0,5);$end=substr($v['confirmed_end_time'],0,5);if($slots->contains(fn($slot)=>$start<substr((string)$slot->confirmed_end_time,0,5)&&$end>substr((string)$slot->confirmed_start_time,0,5))){$validator->errors()->add('confirmed_start_time','That time overlaps an already confirmed appointment.');}});}
}
