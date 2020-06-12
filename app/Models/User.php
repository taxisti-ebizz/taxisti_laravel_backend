<?php

namespace App\Models;

use App\Models\Ratting;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;


    protected $table = 'taxi_users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'profile_pic', 'email_id', 'password', 'login_type', 'date_of_birth', 'mobile_no', 'user_type', 'status', 'created_date', 'updated_date', 'facebook_id', 'device_type', 'device_token', 'fire_base_id', 'verify',
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
        return $this->hasMany(Request::class, 'rider_id', 'user_id')->where('ride_status', 3);
    }

    public function cancel_ride()
    {
        return $this->hasMany(Request::class, 'rider_id', 'user_id')->where('is_canceled', 1)->where('cancel_by', 2);
    }

    public function total_review()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'rider_id', 'request_id', 'user_id')->where('review_by', 'driver');
    }

    public function avg_rating()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'rider_id', 'request_id', 'user_id')->where('review_by', 'driver');
    }

    public function driver_rides()
    {
        return $this->hasMany(Request::class, 'driver_id', 'user_id')->where('ride_status', 3);
    }

    public function driver_cancel_ride()
    {
        return $this->hasMany(Request::class, 'driver_id', 'user_id')->where('is_canceled', 1)->where('cancel_by', 2);
    }

    public function driver_total_review()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'driver_id', 'request_id', 'user_id')->where('review_by', 'rider');
    }

    public function driver_avg_rating()
    {
        return $this->hasManyThrough(Ratting::class, Request::class, 'driver_id', 'request_id', 'user_id')->where('review_by', 'rider');
    }


    public function avgRating()
    {
        return $this->avg_rating()
            ->selectRaw('avg(ratting) as avg, rider_id')
            ->groupBy('rider_id');
    }

    public function getAvgRatingAttribute()
    {
        if ( ! array_key_exists('avgRating', $this->relations)) {
            $this->load('avgRating');
    }

        $relation = $this->getRelation('avgRating')->first();

        return ($relation) ? (int) $relation->aggregate : 0;
    }

}
