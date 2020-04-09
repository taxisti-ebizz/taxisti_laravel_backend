<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'taxi_driver_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;


    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'driver_id','licence','car_brand','car_year','plate_no','car_pic','availability','current_location','latitude','longitude','created_datetime','last_update','profile',
    ];

    public function profile_data()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id');
    }

    public function rides()
    {
        return $this->hasMany(Request::class,'driver_id','driver_id');
    }

    public function cancel_ride()
    {
        return $this->hasMany(Request::class,'driver_id','driver_id');
    }

    public function total_review()
    {
        return $this->hasMany(Ratting::class,'driver_id','driver_id');
    }

    public function avg_rating()
    {
        return $this->hasMany(Ratting::class,'driver_id','driver_id');
    }

}
