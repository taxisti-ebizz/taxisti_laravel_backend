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
use App\Repositories\Api\App\AppCommonRepository;

class RiderRepository extends Controller
{
    protected $appCommon;

    public function __construct()
    {
        $this->appCommon = new AppCommonRepository;
    }

    // get driver
    public function get_driver($request)
    {
        $user_id = Auth()->user()->user_id;
        $latitude = $request['latitude'];
        $longitude = $request['longitude'];
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
                $input['created_date'] = date('Y-m-d H:i:s');

                DB::table('taxi_driver_notavailable')->insert($input);

                $msg['status'] = false;
                $msg['message'] = 'No data found';
                $msg['driver_id'] = '';
            }
        } else {

            $input['rider_id'] = $user_id;
            $input['start_location'] = $start_location;
            $input['created_date'] = date('Y-m-d H:i:s');

            DB::table('taxi_driver_notavailable')->insert($input);

            $msg['status'] = false;
            $msg['message'] = 'No data found';
            $msg['driver_id'] = '';
        }

        return response()->json($msg, 200);
    }

    // request ride
    public function request_ride($request)
    {
        
        $msg = [];
        $rider_id = Auth()->user()->user_id;
        $driver_id = $request['driver_id'];

        //comma seprated driver ids
        $driver_ids = $request['driver_id'];

        if (explode(",", $driver_ids)) {
            $did = explode(",", $driver_ids);
        } else {
            $did[] = $driver_ids;
            $driver_id = $driver_ids;
        }


        $new_driver_array = array();
        $new_driver_ids = '';
        foreach ($did as $ids) {

            if ($this->appCommon->check_driver_availablity($ids)) {
                if ($new_driver_ids != '') {
                    $new_driver_ids .= ',';
                }
                $new_driver_array[] = $ids;
            }
        }

        $rejected_by_ids = [];
        foreach ($new_driver_array as $cur_driver_id) {
            if ($this->appCommon->check_driver_availablity($cur_driver_id)) {
                $driver_id = $cur_driver_id;

                break;
            } else {
                array_push($rejected_by_ids, $cur_driver_id);
            }
        }

        $new_driver_array = array_diff($new_driver_array, $rejected_by_ids);

        $rejected_ids = implode(',', $rejected_by_ids);
        $new_driver_ids = implode(',', $new_driver_array);

        $start_location = $request['start_location'];
        $start_latitude = $request['start_latitude'];
        $start_longitude = $request['start_longitude'];

        $end_location = $request['end_location'];
        $end_latitude = $request['end_latitude'];
        $end_longitude = $request['end_longitude'];

        $passengers = $request['passengers'];
        $note = $request['note'] != '' ? $request['note'] : '';
        $amount = $request['amount'];
        $distance = $request['distance'];


        $input['rider_id'] = $rider_id;
        $input['driver_id'] = $request['driver_id'];
        $input['start_datetime'] = date("Y-m-d H:i:s");
        $input['start_location'] = $start_location;
        $input['start_latitude'] = $start_latitude;
        $input['start_longitude'] = $start_longitude;
        $input['end_location'] = $end_location;
        $input['end_latitude'] = $end_latitude;
        $input['end_longitude'] = $end_longitude;
        $input['passenger'] = $passengers;
        $input['created_date'] = date("Y-m-d H:i:s");
        $input['updated_date'] = date("Y-m-d H:i:s");
        $input['is_canceled'] = 0;
        $input['amount'] = $amount;
        $input['distance'] = $distance;
        $input['rejected_by'] = $rejected_ids;
        $input['all_driver'] = $request['driver_id'];
        $input['note'] = $note;

        $add_request = Request::create($input);
        $req_id = $add_request->id;
        if ($add_request) {

            //send notification to driver based on driver Id.
            $driver_data = $this->appCommon->get_driver($driver_id);
            $device_type = $driver_data['device_type'];

            $msg['status'] = true;
            $msg['message'] = 'Request submitted Successfully';
            $msg['req_id'] = $req_id;
            $msg['all_derives'] = $new_driver_ids;

            if ($this->appCommon->send_request_notification_to_driver($driver_data['device_token'], $req_id, $driver_id, $rider_id, $device_type)) {
                $msg['status'] = true;
                $msg['req_id'] = $req_id;
                $msg['message'] = 'Request submitted Successfully';
            }

            $rider = $this->appCommon->get_rider($rider_id);
            $data['first_name'] = $rider['first_name'];
            $data['last_name'] = $rider['last_name'];
            $data['req_id'] = $req_id;
            $data['all_drivers'] = $request['driver_id'];
            $msg['data'] = $data;
        } else {
            $msg['status'] = false;
            $msg['message'] = 'We are sorry, all our drivers are busy now. Please try again later.';
        }


        return response()->json($msg, 200);
    }
}
