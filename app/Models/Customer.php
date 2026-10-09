<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {
 protected $primaryKey='customer_id'; protected $fillable=['user_id','first_name','last_name','contact_number','address'];
 public function user(){return $this->belongsTo(User::class,'user_id','user_id');} public function devices(){return $this->hasMany(Device::class,'customer_id');} public function appointments(){return $this->hasMany(Appointment::class,'customer_id');}
}
