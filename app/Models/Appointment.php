<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Appointment extends Model {
 protected $primaryKey='appointment_id'; protected $fillable=['customer_id','device_id','preferred_date','preferred_start_time','preferred_end_time','confirmed_date','confirmed_start_time','confirmed_end_time','concern','status'];
 protected $casts=['preferred_date'=>'date','confirmed_date'=>'date'];
 public function customer(){return $this->belongsTo(Customer::class,'customer_id');} public function device(){return $this->belongsTo(Device::class,'device_id');} public function repairRecord(){return $this->hasOne(RepairRecord::class,'appointment_id');}
}
