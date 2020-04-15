<?php

namespace App\Models;

use App\Models\Ratting;
use App\Models\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;


    protected $table = 'taxi_users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = [
        'user_id', 'first_name', 'last_name','profile_pic','email_id','password','login_type','date_of_birth','mobile_no','user_type','status','created_date','updated_date','facebook_id','device_type','device_token','fire_base_id','verify',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function complate_ride()
    {
        return $this->hasMany(Request::class,'rider_id','user_id');
    }

    public function cancel_ride()
    {
        return $this->hasMany(Request::class,'rider_id','user_id');
    }

    public function total_review()
    {
        return $this->hasMany(Ratting::class,'rider_id','user_id');
    }

    public function avg_rating()
    {
        return $this->hasMany(Ratting::class,'rider_id','user_id');
    }
 
    public function driver_rides()
    {
        return $this->hasMany(Request::class,'driver_id','user_id');
    }

    public function driver_cancel_ride()
    {
        return $this->hasMany(Request::class,'driver_id','user_id');
    }
    
    public function driver_total_review()
    {
        return $this->hasMany(Ratting::class,'driver_id','user_id');
    }

    public function driver_avg_rating()
    {
        return $this->hasMany(Ratting::class,'driver_id','user_id');
    }
    
    
}
