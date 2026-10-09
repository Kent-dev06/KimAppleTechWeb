<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Device extends Model {
 protected $primaryKey='device_id'; protected $fillable=['customer_id','device_type','brand','model','serial_number'];
 public function customer(){return $this->belongsTo(Customer::class,'customer_id');} public function appointments(){return $this->hasMany(Appointment::class,'device_id');} public function repairRecords(){return $this->hasMany(RepairRecord::class,'device_id');}
}
