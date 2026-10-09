<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RepairRecord extends Model {
 protected $primaryKey='repair_id'; protected $fillable=['appointment_id','device_id','diagnosis','parts_used','technician_notes','repair_status','cost_estimate','date_completed'];
 protected $casts=['date_completed'=>'date','cost_estimate'=>'decimal:2'];
 public function appointment(){return $this->belongsTo(Appointment::class,'appointment_id');} public function device(){return $this->belongsTo(Device::class,'device_id');}
}
