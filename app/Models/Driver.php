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

    public function driver_rides()
    {
        return $this->hasMany(Request::class,'driver_id','driver_id')->where('ride_status', 3);;
    }

    public function driver_cancel_ride()
    {
        return $this->hasMany(Request::class,'driver_id','driver_id')->where('is_canceled', 1)->where('cancel_by', 2);
    }

    public function driver_total_review()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'driver_id', 'request_id', 'driver_id')->where('review_by', '=', 'rider');
    }

    public function driver_avg_rating()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'driver_id', 'request_id', 'driver_id')->where('review_by', '=', 'rider');
    }


}
