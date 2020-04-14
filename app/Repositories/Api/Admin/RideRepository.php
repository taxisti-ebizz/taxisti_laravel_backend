<?php


namespace App\Repositories\Api\Admin;

use App\Models\Ratting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RideRepository extends Controller
{
    // get pending ride list
    public function get_pending_ride_list($request)
    {
        $pending_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as drider_name'),
                        'driver.mobile_no as driver_mobile'
                    )               
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',0)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($pending_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Pending Rride List', 
                'data'    => $pending_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get running ride list
    public function get_running_ride_list($request)
    {
        $running_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as drider_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',1)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($running_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Running Rride List', 
                'data'    => $running_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get completed ride list
    public function get_completed_ride_list($request)
    {
        $completed_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as drider_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.ride_status',3)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($completed_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Completed Rride List', 
                'data'    => $completed_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get no response ride list
    public function get_no_response_ride_list($request)
    {
        $no_response_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                        DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                            'rider.mobile_no as rider_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.is_canceled',1)
                ->where('taxi_request.cancel_by',0)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($no_response_ride_list['data'])
        {

            foreach ($no_response_ride_list['data'] as  $ride) {
                
                $driverArray = explode(',', $ride->rejected_by);

                // get driver data
                $driver_name = DB::table('taxi_users')
                    ->select( 
                        DB::raw('CONCAT(first_name," ",last_name) as rider_name'),
                            'mobile_no as rider_mobile'
                        )
                    ->whereIn('user_id',$driverArray)
                    ->get();
                                        
                // format data
                $driverName = '';
                $driverMobile = '';
                foreach ($driver_name as $value) {
                    if($driverMobile!='')
                    {
                        $driverMobile .= ',';
                    }
                    $driverMobile .= $value->rider_mobile;


                    if($driverName!='')
                    {
                        $driverName .= ', ';
                    }
                    $driverName .= $value->rider_name;
                }

                $ride->driver_name = $driverName;
                $ride->driver_mobile = $driverMobile;

                $ride_list[] = $ride;
            }

            $no_response_ride_list['data'] = $ride_list;

            return response()->json([
                'status'    => true,
                'message'   => 'No Response Rride List', 
                'data'    => $no_response_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get canceled ride list
    public function get_canceled_ride_list($request)
    {
        $canceled_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as drider_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',3)
                ->where('taxi_request.is_canceled',1)
                ->whereIn('taxi_request.cancel_by',[1,2])
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($canceled_ride_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Canceled Rride List', 
                'data'    => $canceled_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get no driver available list
    public function get_no_driver_available_list($request)
    {
        $no_driver_available_list = DB::table('taxi_driver_notavailable')
                ->select('taxi_driver_notavailable.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile'
                    )
                ->join('taxi_users as rider', 'taxi_driver_notavailable.rider_id', '=', 'rider.user_id')
                ->orderByRaw('taxi_driver_notavailable.id DESC')
                ->paginate(10)->toArray();

        if($no_driver_available_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'no driver available list', 
                'data'    => $no_driver_available_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get fake ride list
    public function get_fake_ride_list($request)
    {
        $fake_ride_list = DB::table('taxi_request')
                ->select('taxi_request.*', 
                    DB::raw('CONCAT(rider.first_name," ",rider.last_name) as rider_name'),
                        'rider.mobile_no as rider_mobile', 
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as drider_name'),
                        'driver.mobile_no as driver_mobile'
                    )
                ->leftJoin('taxi_users as rider', 'taxi_request.rider_id', '=', 'rider.user_id')
                ->leftJoin('taxi_users as driver', 'taxi_request.driver_id', '=', 'driver.user_id')
                ->where('taxi_request.status',4)
                ->orderByRaw('taxi_request.id DESC')
                ->paginate(10)->toArray();

        if($fake_ride_list['data'])
        {

            foreach ($fake_ride_list['data'] as  $ride) {
                
                $rider_reveiw = Ratting::where('request_id',$ride->id)->where('review_by','rider')->value('ratting');
                $driver_reveiw = Ratting::where('request_id',$ride->id)->where('review_by','driver')->value('ratting');
                
                $ride->rider_reveiw = $rider_reveiw != "" ? $rider_reveiw : 0;
                $ride->driver_reveiw = $driver_reveiw != "" ? $driver_reveiw : 0;
                
                $ride_list[] = $ride;
            }
            $fake_ride_list['data'] = $ride_list;

            return response()->json([
                'status'    => true,
                'message'   => 'Fake Rride List', 
                'data'    => $fake_ride_list,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }


}