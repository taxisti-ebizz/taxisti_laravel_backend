<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverOnlineHours extends Model
{
    protected $table = 'taxi_driver_online_hours';
    protected $primaryKey = 'id';
    public $timestamps = false;


    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'id','driver_id','created_date','start_time','end_time'];

}
