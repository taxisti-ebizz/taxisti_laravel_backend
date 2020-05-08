<?php


namespace App\Repositories\Api\App;

use App\GCM;
use App\Models\User;
use App\Models\Driver;
use App\Models\Ratting;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class RiderRepository extends Controller
{
    // get driver
    public function get_driver($request)
    {
        $latitude = $request['latitude'];
        $longitude = $request['longitude'];
        $user_id = $request['user_id'];
        $start_location = $request['start_location'];

        $getOption = DB::table('taxi_option')->get();
        if ($getOption) {
            $km = 0;
            foreach ($getOption as $option) {
                if ($option->option_name == 'radius') {
                    $km .= $option->option_value / 1000;
                }
            }
        }

        $distance_data = DB::table('taxi_driver_detail')
            ->select(
                DB::raw("distinct(taxi_driver_detail.`driver_id`),(((acos(sin(( $latitude*pi()/180)) * sin((`latitude`*pi()/180))+cos(( $latitude*pi()/180)) * cos((`latitude`*pi()/180)) * cos((($longitude - `longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance")
            )
            ->join('taxi_users', 'taxi_users.user_id', 'taxi_driver_detail.driver_id')
            ->where('taxi_driver_detail.availability', 1)
            ->where('taxi_users.user_type', 1)
            ->having('distance', '<', $km)
            ->get();

        if ($distance_data) {
            $driver = [];
            foreach ($distance_data as $result) {
                $list['driver_id'] = $result->driver_id;
                $list['distance'] = $result->distance;
                $driver[] = $list;
            }

            usort($driver, function ($a, $b) {
                return $b['distance'] <= $a['distance'];
            });

            $driverId = '';
            $driverAll = array();
            foreach ($driver as $val) {

                if ($driverId != '') {
                    $driverId .= ',';
                }
                $driverId .= $val['driver_id'];

                $driverAll[] = $val['driver_id'];
            }

            $user = Request::whereIn('driver_id', explode(',', $driverId))->where('status', 1)->where('is_canceled', 0)->get();
            $abc = array();
            if ($user) {
                foreach ($user as $userResult) {

                    if (!in_array($userResult->driver_id, $abc)) {
                        $abc[] = $userResult->driver_id;
                    }
                }
            }


            $xyz = array();
            if (!empty($abc)) {
                foreach ($driverAll as $val_driver) {
                    if (!in_array($val_driver, $abc)) {
                        $xyz[] = $val_driver;
                    }
                }
            } else {
                $xyz = $driverAll;
            }

            if (!empty($xyz)) {
                $dri_id = implode(',', $xyz);

                $msg['status'] = true;
                $msg['message'] = 'Success';
                $msg['driver_id'] = $dri_id;
            } else {
                $input['rider_id'] = $user_id;
                $input['start_location'] = $start_location;
                $input['created_date'] = date('Y-m-d H:m:d');

                DB::table('taxi_driver_notavailable')->insert($input);

                $msg['status'] = false;
                $msg['message'] = 'No data found';
                $msg['driver_id'] = '';
            }


        } else {

            $input['rider_id'] = $user_id;
            $input['start_location'] = $start_location;
            $input['created_date'] = date('Y-m-d H:m:d');

            DB::table('taxi_driver_notavailable')->insert($input);

            $msg['status'] = false;
            $msg['message'] = 'No data found';
            $msg['driver_id'] = '';
        }

        return response()->json($msg, 200);
    }
}
