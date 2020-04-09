<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'taxi_request';
    protected $primaryKey = 'id';
    public $timestamps = false;


    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'rider_id','driver_id','start_datetime','status','all_driver','rejected_by','end_datetime','start_location','start_latitude','start_longitude','end_location','end_latitude','end_longitude','passenger','created_date','updated_date','is_canceled','amount','distance','note','ride_status','last_cron','cancel_by','ride_status_text','cancel_text',
    ];


}
