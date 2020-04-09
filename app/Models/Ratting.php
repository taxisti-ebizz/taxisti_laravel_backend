<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ratting extends Model
{
    protected $table = 'taxi_ratting';
    protected $primaryKey = 'id';
    public $timestamps = false;


    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'request_id','driver_id','rider_id','ratting','review','created_date','created_date',
    ];
}
