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
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
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
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
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
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
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
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
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
                    DB::raw('CONCAT(driver.first_name," ",driver.last_name) as driver_name'),
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
                'message'   => 'Fake ride list', 
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

    // get ride area list
    public function get_ride_area_list($request)
    {
        $ride_area_list = DB::table('taxi_ride_area_coordinates')
                ->select('id','area_name','created_date')
                ->orderByRaw('id DESC')
                ->paginate(10)->toArray();

        if($ride_area_list['data'])
        {

            return response()->json([
                'status'    => true,
                'message'   => 'Rride area list', 
                'data'    => $ride_area_list,
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

    // view area boundaries
    public function view_area_boundaries($request)
    {
        $view_area_boundaries = DB::table('taxi_ride_area_coordinates')
                ->where('id',$request->id)
                ->first();

        if($view_area_boundaries)
        {
            $result = (array)$view_area_boundaries;
            $areaStr = $center = '';
            $centerLatLng = '53.7267,-127.6476';
            $centerLatLng = explode(',', $centerLatLng);
            $lat = $centerLatLng[0];
            $long = $centerLatLng[1];
            $area = (!empty($result['coordinates'])) ? $result['coordinates'] : '';
            
            if (!empty($area)) {
                $areaLatLong = str_replace('"', '', $area);
                $area = str_replace('[', '', $area);
                $area = str_replace(']', '', $area);
                $area = str_replace('{', '', $area);
                $area = str_replace('}', '', $area);
                if(strpos($area, '.')!==false)
                {
                    $area = explode(',', $area);
    
    
                    $aa = '';
                    
                    $i=0;
                    foreach ($area as $value) {
                        $val = explode(':', $value);
    
                        if($aa!='')
                        {
                            $aa .= ',';
    
                        }
                        if($i % 2 == 0)
                        {
                            $aa .= '{';
                        }
                        $aa .= $val[0].':'.$val[1];
                        if($i % 2 != 0)
                        {
                            $aa .= '}';
                        }
                        
                    $i++;
                    }
    
                }
    
                $areaStr = str_replace('{', '(', $areaLatLong);
                $areaStr = str_replace('}', ')', $areaStr);
                $areaStr = str_replace('),(', ')|(', $areaStr);
                $areaStr = str_replace('[', '', $areaStr);
                $areaStr = str_replace(']', '', $areaStr);
                
                $center = explode('|', $areaStr);
                if (!empty($center)) {
                    if (!empty($center[0])) {
    
                        $centerLatLng = str_replace('(lat:', '', $center[0]);
                        $centerLatLng = str_replace('lng:', '', $centerLatLng);
                        $centerLatLng = str_replace(')', '', $centerLatLng);
    
                       $centerLatLng = explode(',', $centerLatLng);
                       $lat = $centerLatLng[0];
                       $long = $centerLatLng[1];
                    }
                }
                
                $areaStr = str_replace('lat', '', $areaStr);
                $areaStr = str_replace('lng', '', $areaStr);
                $areaStr = str_replace(':', '', $areaStr);
                $areaStr = str_replace('"', '', $areaStr);
    
                $array['areaStr'] = $areaStr;
                $array['lat'] = $lat;
                $array['long'] = $long;
                $array['area'] = $aa;
        
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Area Boundaries', 
                'data'    => $array,
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