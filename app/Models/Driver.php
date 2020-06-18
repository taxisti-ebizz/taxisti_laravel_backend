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
        return $this->belongsTo(User::class, 'driver_id', 'driver_id');
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

    public function driverAvgRating()
    {
        return $this->driver_avg_rating()
            ->selectRaw('ROUND(coalesce(avg(ratting),0),1) as avg, driver_id')
            ->groupBy('driver_id');
    }

    public function onlineHoursCurrentWeekRow()
    {
        $previous_week = strtotime("0 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week);
        $end_week = strtotime("next friday",$start_week);
        $start_week = date("Y-m-d",$start_week);
        $end_week = date("Y-m-d",$end_week);

        return $this->hasMany(DriverOnlineHours::class, 'driver_id', 'driver_id')->where('end_time','!=','00:00:00')->whereBetween('created_date', [$start_week, $end_week]);
    }

    public function onlineHoursLastWeekRow()
    {
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week);
        $end_week = strtotime("next friday",$start_week);
        $start_week = date("Y-m-d",$start_week);
        $end_week = date("Y-m-d",$end_week);

        return $this->hasMany(DriverOnlineHours::class, 'driver_id', 'driver_id')->where('end_time','!=','00:00:00')->whereBetween('created_date', [$start_week, $end_week]);
    }

    public function totalonlineHoursRow()
    {
        return $this->hasMany(DriverOnlineHours::class, 'driver_id', 'driver_id')->where('end_time','!=','00:00:00');
    }

    public function onlineHoursCurrentWeek()
    {
        return $this->onlineHoursCurrentWeekRow()
        ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))) as time, driver_id')
        ->groupBy('driver_id');
    }
    
    public function onlineHoursLastWeek()
    {
        return $this->onlineHoursLastWeekRow()
        ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))) as time, driver_id')
        ->groupBy('driver_id');
    }

    public function totalOnlineHours()
    {
        return $this->totalonlineHoursRow()
        ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))) as time, driver_id')
        ->groupBy('driver_id');
    }

}
