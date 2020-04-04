<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $guard = 'admin';

    protected $table = 'taxi_admin';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = [
        'name', 'email_id', 'password','type','mobile_no','status','is_deleted','lastupdated_date','lastupdated_time','deleted_datetime'
    ];
}
