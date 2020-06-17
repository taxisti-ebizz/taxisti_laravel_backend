<?php


namespace App\Repositories\Api\Admin;

use File;
use ArrayObject;
use App\Models\User;
use App\Models\Driver;
use App\Models\Ratting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class DriverRepository extends Controller
{

    // get driver list
    public function get_driver_list($request)
    {
        $driver_list = array();
        if($request['type'] == 'select' && $request['sub_type'] == '') 
        {
            // Select driver list
            $list = 'Select';
        
            $driver_list = Driver::select(
                    DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as driver_name'),
                    'taxi_users.mobile_no as driver_mobile','taxi_users.user_id as driver_id'
                )
                ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
                ->orderByRaw('taxi_users.user_id DESC')
                ->get();
        }
        elseif($request['type'] == 'current' && $request['sub_type'] == '') 
        {
            // current driver
            $list = 'Current';
        
            $driver_list = Driver::select('taxi_driver_detail.*','taxi_users.*')
                ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
                ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                ->with('driverAvgRating')
                ->orderByRaw('taxi_users.user_id DESC')
                ->paginate(10)->toArray();
        }
        elseif($request['type'] == 'online' && $request['sub_type'] == '')
        {
            // online driver
            $list = 'Online';

            $url="https://taxisti-8392c.firebaseio.com/userData1.json";

            $ch = curl_init();
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL,$url);
            // Execute
            $result=curl_exec($ch);
            // Closing
            curl_close($ch);

                        
            $ids = '';
            if(!empty($result))
            {
                $datas = json_decode($result);
                foreach ($datas as $key => $value) 
                {
                    if($ids!='')
                    {
                        $ids .= ',';
                    }
                    $ids .= $key;
                }
            }
            if($ids == '')
            {
                $ids = 0;
            }


            $driver_id = $ids;
            $driverArray = explode(',', $driver_id);

            $driver_list = Driver::select('taxi_driver_detail.*','taxi_users.*')
            ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
            ->whereIn('taxi_users.user_id', $driverArray)
            ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
            ->with('driverAvgRating')
            ->orderByRaw('taxi_users.user_id DESC')
            ->paginate(10)->toArray();

        }
        elseif($request['type'] == 'currentWeek' && $request['sub_type'] == '')
        {
            // currentWeek driver
            $list = 'CurrentWeek';

            $previous_week = strtotime("0 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week);
            $end_week = strtotime("next friday",$start_week);
            $start_current_week = date("Y-m-d H:i:s",$start_week);
            $end_current_week = date("Y-m-d 23:59:00",$end_week);

            $driver_list = User::select('taxi_driver_detail.*','taxi_users.*')
            ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
            ->where('taxi_users.user_type',1)
            ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
            ->with('driverAvgRating')
            ->whereBetween('taxi_users.created_date', [$start_current_week, $end_current_week])
            ->orderByRaw('taxi_users.user_id DESC')
            ->paginate(10)->toArray();

        }
        elseif($request['type'] == 'lastWeek' && $request['sub_type'] == '')
        {
            // lastWeek driver
            $list = 'LastWeek';

            $previous_week1 = strtotime("-1 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week1);
            $end_week = strtotime("next friday",$start_week);
            $start_last_week = date("Y-m-d H:i:s",$start_week);
            $end_last_week = date("Y-m-d 23:59:00",$end_week);

            $driver_list = User::select('taxi_driver_detail.*','taxi_users.*')
            ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
            ->where('taxi_users.user_type',1)
            ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
            ->with('driverAvgRating')
            ->whereBetween('taxi_users.created_date', [$start_last_week, $end_last_week])
            ->orderByRaw('taxi_users.user_id DESC')
            ->paginate(10)->toArray();

        }
        elseif($request['sub_type'] == 'filter')
        {
            if(isset($request['filter']))
            {

                $filter = json_decode($request['filter']);
                $query = [];

                if($request['type'] == 'current')
                {
                    $list = 'Filter current';

                    $query = Driver::select('taxi_driver_detail.*','taxi_users.*')
                    ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
                    ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                    ->with('driverAvgRating');


                }
                elseif($request['type'] == 'online')
                {
                    $list = 'Filter online';

                    // online driver
                    $list = 'Online';

                    $url="https://taxisti-8392c.firebaseio.com/userData1.json";
        
                    $ch = curl_init();
                    // Will return the response, if false it print the response
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    // Set the url
                    curl_setopt($ch, CURLOPT_URL,$url);
                    // Execute
                    $result=curl_exec($ch);
                    // Closing
                    curl_close($ch);
        
                                
                    $ids = '';
                    if(!empty($result))
                    {
                        $datas = json_decode($result);
                        foreach ($datas as $key => $value) 
                        {
                            if($ids!='')
                            {
                                $ids .= ',';
                            }
                            $ids .= $key;
                        }
                    }
                    if($ids == '')
                    {
                        $ids = 0;
                    }
        
        
                    $driver_id = $ids;
                    $driverArray = explode(',', $driver_id);
        
                    $query = Driver::select('taxi_driver_detail.*','taxi_users.*')
                    ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
                    ->whereIn('taxi_users.user_id', $driverArray)
                    ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                    ->with('driverAvgRating');

        
                }
                elseif($request['type'] == 'currentWeek')
                {
                    $list = 'Filter currentWeek';

                    $previous_week = strtotime("0 week +1 day");
                    $start_week = strtotime("last saturday midnight",$previous_week);
                    $end_week = strtotime("next friday",$start_week);
                    $start_current_week = date("Y-m-d H:i:s",$start_week);
                    $end_current_week = date("Y-m-d 23:59:00",$end_week);
        
                    $query = User::select('taxi_driver_detail.*','taxi_users.*')
                    ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
                    ->where('taxi_users.user_type',1)
                    ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                    ->with('driverAvgRating')
                    ->whereBetween('taxi_users.created_date', [$start_current_week, $end_current_week]);
                }
                elseif($request['type'] == 'lastWeek')
                {
                    $list = 'filter lastWeek';

                    $previous_week1 = strtotime("-1 week +1 day");
                    $start_week = strtotime("last saturday midnight",$previous_week1);
                    $end_week = strtotime("next friday",$start_week);
                    $start_last_week = date("Y-m-d H:i:s",$start_week);
                    $end_last_week = date("Y-m-d 23:59:00",$end_week);
        
                    $query = User::select('taxi_driver_detail.*','taxi_users.*')
                    ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
                    ->where('taxi_users.user_type',1)
                    ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                    ->with('driverAvgRating')
                    ->whereBetween('taxi_users.created_date', [$start_last_week, $end_last_week]);
                }
                else
                {
                    $list = 'Filter all';

                    $query = User::select('taxi_driver_detail.*','taxi_users.*')
                        ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
                        ->where('taxi_users.user_type',1)
                        ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                        ->with('driverAvgRating');
                        
                }

                if(!empty($filter->username)) // username filter
                {
                    $query->where('first_name', 'LIKE', '%'.$filter->username.'%')->orWhere('last_name', 'LIKE', '%'.$filter->username.'%');
                }

                if(!empty($filter->mobile)) // mobile filter 
                {
                    $query->where('mobile_no', 'LIKE', '%'.$filter->mobile.'%');
                }

                if(!empty($filter->dob)) // date_of_birth filter
                {
                    $query->whereBetween('date_of_birth',explode(' ',$filter->dob));
                }

                if(!empty($filter->dor)) // date_of_register
                {
                    $query->whereBetween('created_date',explode(' ',$filter->dor));
                }

                if(!empty($filter->device_type)) // device_type filter
                {
                    $device_type = explode(',',$filter->device_type);
                    if(count($device_type) > 1)
                    {
                        $query->whereBetween('device_type',$device_type);
                    }
                    else
                    {
                        $query->where('device_type',$device_type[0]);
                    }
                }

                if(!empty($filter->verify)) // verify filter
                {
                    $verify = explode(',',$filter->verify);
                    if(count($verify) > 1)
                    {
                        $query->whereBetween('verify',[0,1]);
                    }
                    {
                        $verify = $verify[0] == 2 ? 0 : 1; 
                        $query->where('verify',$verify);
                    }
                }

                if(!empty($filter->driver_rides)) // driver_rides filter
                {
                    $driver_rides = explode('-',$filter->driver_rides);
                    $query = $query->where(function($q) use ( $driver_rides ){
                        $q->has('driver_rides','>=',$driver_rides[0]);
                        $q->has('driver_rides','<=',$driver_rides[1]);
                    });
                }

                if(!empty($filter->driver_cancel_ride)) // driver_cancel_ride filter
                {
                    $driver_cancel_ride = explode('-',$filter->driver_cancel_ride);
                    $query = $query->where(function($q) use ( $driver_cancel_ride ){
                        $q->has('driver_cancel_ride','>=',$driver_cancel_ride[0]);
                        $q->has('driver_cancel_ride','<=',$driver_cancel_ride[1]);
                    });
                }

                if(!empty($filter->driver_total_review)) // driver_total_review filter
                {
                    $driver_total_review = explode('-',$filter->driver_total_review);
                    $query = $query->where(function($q) use ( $driver_total_review ){
                        $q->has('driver_total_review','>=',$driver_total_review[0]);
                        $q->has('driver_total_review','<=',$driver_total_review[1]);
                    });
                }

                if(!empty($filter->driver_avg_rating)) // driver_avg_rating filter
                {
                    $driver_avg_rating = explode('-',$filter->driver_avg_rating);
                    $query->whereHas('driver_avg_rating' , function ($q) use ( $driver_avg_rating ) {
                        $q->havingRaw('AVG(taxi_ratting.ratting) >= '.$driver_avg_rating[0]);
                        $q->havingRaw('AVG(taxi_ratting.ratting) <= '.$driver_avg_rating[1]);
                    });

                }

                // if(!empty($filter->rejected_ratio)) // rejected_ratio filter
                // {
                //     $rejected_ratio = explode('-',$filter->rejected_ratio);
                //     $query->whereHas('driverAcceptanceRatio' , function ($q) use ( $rejected_ratio ) {
                //         $q->havingRaw('count(id) >= '.$rejected_ratio[0]);
                //         $q->havingRaw('count(id) <= '.$rejected_ratio[1]);
                //     });


                // }
                $driver_list = $query->orderByRaw('taxi_users.user_id DESC')->paginate(10)->toArray();

                if($driver_list['data'])
                {
                    foreach($driver_list['data'] as $driver)
                    {
                        
                        // add calculation
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
    
                        // hours calculation
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';
        
                        if($request['type'] == 'online')
                        {
                            $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
                        }
            
                        $data[] = $driver;
            
                    }
                    $driver_list['data'] = $data; 
                }
                else
                {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'No data available', 
                        'data'    => new ArrayObject,
                    ], 200);
                }
                

            }
            else
            {
                return response()->json([
                    'status'    => false,
                    'message'   => 'filter parameter is required',
                    'data'    => new ArrayObject,
                ], 200);
            }

        }
        else {
            // all driver
            $list = 'All';
            $driver_list = User::select('taxi_driver_detail.*','taxi_users.*')
                ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
                ->where('taxi_users.user_type',1)
                ->withCount('driver_rides','driver_cancel_ride','driver_total_review')
                ->with('driverAvgRating')
                ->orderByRaw('taxi_users.user_id DESC')
                ->paginate(10)->toArray();
        }

        if($request['type'] == 'select')
        {
            return response()->json([
                'status'    => true,
                'message'   =>  $list.' driver list', 
                'data'    => $driver_list,
            ], 200);
        }
        elseif($driver_list['data'])
        {

            if($request['sub_type1'] == 'filter')
            {
                
                $filter = json_decode($request['filter']);
                $data = [];

                
                if(!empty($filter->rejected_ratio)) // rejected_ratio filter
                {
                    $rejected_ratio = explode('-',$filter->rejected_ratio);
                    
                    foreach($driver_list['data'] as $driver)
                    {
                        // add calculation
                        $ratting_review = $this->driver_ratting_review($driver['user_id']);
                        $driver['driver_total_review_count'] = $ratting_review->total_review;
                        $driver['driver_avg_rating_count'] = $ratting_review->avg_ratting;
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';

                        // reviews
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
            
                        // rejected_ratio filter
                        if($driver['rejected_ratio'] >= $rejected_ratio[0] && $driver['rejected_ratio'] <= $rejected_ratio[1])
                        {
                            $data[] = $driver;
                        }
            
                    }
                    $driver_list['data'] = $data; 
                }

                if(!empty($filter->acceptance_ratio)) // acceptance_ratio filter
                {
                    $acceptance_ratio = explode('-',$filter->acceptance_ratio);
                    
                    foreach($driver_list['data'] as $driver)
                    {
                        // add calculation
                        $ratting_review = $this->driver_ratting_review($driver['user_id']);
                        $driver['driver_total_review_count'] = $ratting_review->total_review;
                        $driver['driver_avg_rating_count'] = $ratting_review->avg_ratting;
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';

                        // reviews
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
            
                        // acceptance_ratio filter
                        if($driver['acceptance_ratio'] >= $acceptance_ratio[0] && $driver['acceptance_ratio'] <= $acceptance_ratio[1])
                        {
                            $data[] = $driver;
                        }
            
                    }
                    $driver_list['data'] = $data; 
                }

                if(!empty($filter->online_hours_last_week)) // online_hours_last_week filter
                {
                    $online_hours_last_week = explode('-',$filter->online_hours_last_week);
                    
                    foreach($driver_list['data'] as $driver)
                    {
                        // add calculation
                        $ratting_review = $this->driver_ratting_review($driver['user_id']);
                        $driver['driver_total_review_count'] = $ratting_review->total_review;
                        $driver['driver_avg_rating_count'] = $ratting_review->avg_ratting;
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';

                        // reviews
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
            
                        // online_hours_last_week filter
                        if($driver['online_hours_last_week'] >= $online_hours_last_week[0] && $driver['online_hours_last_week'] <= $online_hours_last_week[1])
                        {
                            $data[] = $driver;
                        }
            
                    }
                    $driver_list['data'] = $data; 
                }

                if(!empty($filter->online_hours_current_week)) // online_hours_current_week filter
                {
                    $online_hours_current_week = explode('-',$filter->online_hours_current_week);
                    
                    foreach($driver_list['data'] as $driver)
                    {
                        // add calculation
                        $ratting_review = $this->driver_ratting_review($driver['user_id']);
                        $driver['driver_total_review_count'] = $ratting_review->total_review;
                        $driver['driver_avg_rating_count'] = $ratting_review->avg_ratting;
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';

                        // reviews
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
            
                        // online_hours_current_week filter
                        if($driver['online_hours_current_week'] >= $online_hours_current_week[0] && $driver['online_hours_current_week'] <= $online_hours_current_week[1])
                        {
                            $data[] = $driver;
                        }
            
                    }
                    $driver_list['data'] = $data; 
                }

                if(!empty($filter->total_online_hours)) // total_online_hours filter
                {
                    $total_online_hours = explode('-',$filter->total_online_hours);
                    
                    foreach($driver_list['data'] as $driver)
                    {
                        // add calculation
                        $ratting_review = $this->driver_ratting_review($driver['user_id']);
                        $driver['driver_total_review_count'] = $ratting_review->total_review;
                        $driver['driver_avg_rating_count'] = $ratting_review->avg_ratting;
                        $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                        $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                        $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];
                        $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                        $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                        $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
        
                        // add car images
                        $driver['car_images'] = $this->car_images($driver['id']);
        
                        // add base url
                        $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                        $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                        $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';

                        // reviews
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
            
                        // total_online_hours filter
                        if($driver['total_online_hours'] >= $total_online_hours[0] && $driver['total_online_hours'] <= $total_online_hours[1])
                        {
                            $data[] = $driver;
                        }
            
                    }
                    $driver_list['data'] = $data; 
                }

                if(empty($driver_list['data'])) // No user found
                {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'No user found',
                        'data'    => new ArrayObject,
                    ], 200);
                }

            }
            else
            {
                foreach($driver_list['data'] as $driver)
                {
                    
                    // add calculation
                    $ratio = $this->acceptance_rejected_ratio($driver['user_id']);
                    $driver['rejected_ratio'] = $ratio['rejected_ratio']; 
                    $driver['acceptance_ratio'] = $ratio['acceptance_ratio'];

                    // hours calculation
                    $driver['online_hours_last_week'] = $this->total_online_hours_lastweek($driver['user_id']);
                    $driver['online_hours_current_week'] = $this->total_online_hours_currentweek($driver['user_id']);
                    $driver['total_online_hours'] = $this->total_online_hours($driver['user_id']);
    
                    // add car images
                    $driver['car_images'] = $this->car_images($driver['id']);
    
                    // add base url
                    $driver['licence'] = $driver['licence'] != ''? env('AWS_S3_URL').$driver['licence'] : '';
                    $driver['profile'] = $driver['profile'] != ''? env('AWS_S3_URL').$driver['profile'] : '';
                    $driver['profile_pic'] = $driver['profile_pic'] != ''? env('AWS_S3_URL').$driver['profile_pic'] : '';
    
                    if($request['type'] == 'online')
                    {
                        $driver['reviews'] = $this->getDriverRatRevData($driver['user_id']);
                    }
        
                    $data[] = $driver;
        
                }
                $driver_list['data'] = $data; 
            }
    
            return response()->json([
                'status'    => true,
                'message'   =>  $list.' driver list', 
                'data'    => $driver_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get driver detail
    public function get_driver_detail($request)
    {
        $driver = DB::table('taxi_driver_detail')
        ->select('taxi_driver_detail.*','taxi_users.*')
        ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
        ->where('taxi_driver_detail.driver_id',$request->driver_id)
        ->first();

        if($driver)
        {
            $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
            $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
            $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';

            // add car images
            $driver->car_images = $this->car_images($driver->id);

               
            return response()->json([
                'status'    => true,
                'message'   => 'driver detail', 
                'data'    => $driver,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No driver found', 
                'error'    => '',
            ], 200);
        }
    }

    // edit driver detail
    public function edit_driver_detail($request)
    {

        $driver_detail = Driver::where('driver_id',$request['driver_id'])->first();

        // check driver exists 
        if($driver_detail)
        {
            // profile_pic handling 
            if($request->file('profile_pic')){

                // delete files
                Storage::disk('s3')->exists($driver_detail->profile) ? Storage::disk('s3')->delete($driver_detail->profile) : '';

                $profile_pic = $request->file('profile_pic');
                $imageName = 'uploads/driver_images/'.time().'.'.$profile_pic->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');

                $input['profile_pic'] = $imageName;
                $driver['profile'] = $imageName;
                                        
            }

            // licence handling 
            if($request->file('licence')){

                // delete files
                Storage::disk('s3')->exists($driver_detail->licence) ? Storage::disk('s3')->delete($driver_detail->licence) : '';

                $licence = $request->file('licence');
                $imageName = 'uploads/licence_images/'.time().'.'.$licence->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($licence), 'public');

                $driver['licence'] = $imageName;
                                        
            }

            // car_image handling 
            if($request->file('car_image')){

                foreach ($request->file('car_image') as  $car_image) {

                    // $car_image = $request->file('car_image');
                    $imageName = 'uploads/car_images/'.time().'.'.$car_image->getClientOriginalExtension();
                    $img = Storage::disk('s3')->put($imageName, file_get_contents($car_image), 'public');

                    $car['driver_detail_id'] = $driver_detail->id;  
                    $car['image'] = $imageName;  
                    $car['datetime'] = date('Y-m-d H:i:s');  
                    DB::table('taxi_car_images')->insert($car);
                }

            }

            // update driver detail data
            $driver['car_brand'] = $request['car_brand'];
            $driver['car_year'] = $request['car_year'];
            $driver['plate_no'] = $request['plate_no'];
            $driver['last_update'] = date('Y-m-d H:i:s');
            Driver::where('driver_id',$request['driver_id'])->update($driver);
        }
        else
        {
            // insert driver detail data
            $driver['driver_id'] = $request['driver_id'];
            $driver['car_brand'] = $request['car_brand'];
            $driver['car_year'] = $request['car_year'];
            $driver['plate_no'] = $request['plate_no'];
            $driver['last_update'] = date('Y-m-d H:i:s');
            Driver::where('driver_id',$request['driver_id'])->insert($driver);

            $driver_detail = Driver::where('driver_id',$request['driver_id'])->first();

            // profile_pic handling 
            if($request->file('profile_pic')){
                
                $profile_pic = $request->file('profile_pic');
                $imageName = 'uploads/driver_images/'.time().'.'.$profile_pic->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');

                $input['profile_pic'] = $imageName;
                $driver['profile'] = $imageName;
                                    
            }

            // licence handling 
            if($request->file('licence')){

                $licence = $request->file('licence');
                $imageName = 'uploads/licence_images/'.time().'.'.$licence->getClientOriginalExtension();
                $img = Storage::disk('s3')->put($imageName, file_get_contents($licence), 'public');

                $driver['licence'] = $imageName;
                                    
            }

            // car_image handling 
            if($request->file('car_image')){

                foreach ($request->file('car_image') as  $car_image) {

                    // $car_image = $request->file('car_image');
                    $imageName = 'uploads/car_images/'.time().'.'.$car_image->getClientOriginalExtension();
                    $img = Storage::disk('s3')->put($imageName, file_get_contents($car_image), 'public');

                    $car['driver_detail_id'] = $driver_detail->id;  
                    $car['image'] = $imageName;  
                    $car['datetime'] = date('Y-m-d H:i:s');  
                    DB::table('taxi_car_images')->insert($car);
                }

            }

            Driver::where('driver_id',$request['driver_id'])->update($driver);

        }


        // update driver data
        $input['first_name'] = $request['first_name'];
        $input['last_name'] = $request['last_name'];
        $input['updated_date'] = date('Y-m-d H:i:s');
        User::where('user_id',$request['driver_id'])->update($input);
        
        // get driver details
        $get_driver_detail = $this->get_driver_detail($request);

        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $get_driver_detail->original['data'],
        ], 200);
        
    }

    // edit driver status
    public function edit_driver_status($request)
    {
        $input = $request->except(['driver_id']);
        $input['updated_date'] = date('Y-m-d H:i:s');

        // update status
        User::where('user_id',$request['driver_id'])->update($input);
        $user =  User::where('user_id', $request['driver_id'])->first();

        if($request['verify']==1)
		{
			$message='Hi '.$user['first_name'].", Your Account Is verified By Administrator.";
		}
		else{
			$message='Hi '.$user['first_name'].", Your Account Is Deactivated By Administrator.";
        }
        
        $device_type = $user['device_type'];
		$device_token = $user['device_token'];
        $type = 'account_verify';
        
		if($this->sentNotificationOnVerified($message,$device_token,$type, $device_type))
		{
            $this->store_notification($request['driver_id'],'verify_user',$message);
		}

        
        // get driver details
        $get_driver_detail = $this->get_driver_detail($request);

        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $get_driver_detail->original['data'],
        ], 200);
        
    }

    // delete driver
    public function delete_driver($request ,$driver_id)
    {
        $driver = Driver::where('driver_id',$driver_id)->first();
        $user = User::where('user_id',$driver_id)->first();
        
        $licence_image_path = $driver['licence']; 
        $profile_pic_image_path = $user['profile_pic']; 
        
        // delete files
        Storage::disk('s3')->exists($licence_image_path) ? Storage::disk('s3')->delete($licence_image_path) : '';
        Storage::disk('s3')->exists($profile_pic_image_path) ? Storage::disk('s3')->delete($profile_pic_image_path) : '';
        $this->delete_car_images($driver['id']);

        Driver::where('driver_id',$driver_id)->delete();
        User::where('user_id',$driver_id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'driver deleted', 
            'data'    => '',
        ], 200);   
    }

    // delete driver image
    public function delete_car_image($request ,$id)
    {
        $image = DB::table('taxi_car_images')->where('id',$id)->first();

        $car_image_path = $image->image; 

        // delete files
        Storage::disk('s3')->exists($car_image_path) ? Storage::disk('s3')->delete($car_image_path) : '';

        DB::table('taxi_car_images')->where('id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Car image deleted', 
            'data'    => '',
        ], 200);   
    }


    // get driver reviews
    public function get_driver_reviews($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {

                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter';

                $query = User::select(
                    DB::raw('CONCAT(first_name," ",last_name) as driver_name'),
                        'mobile_no as driver_mobile','user_id as driver_id'
                    )
                    ->withCount('driver_total_review')
                    ->withCount([
                        'driver_avg_rating' => function ($query) {
                            $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                        }
                    ])
                    ->where('user_type',1);
                    

                if(!empty($filter->name)) // name filter
                {
                    $query->where('first_name', 'LIKE', '%'.$filter->name.'%')->orWhere('last_name', 'LIKE', '%'.$filter->name.'%');
                }

                if(!empty($filter->mobile)) // mobile filter 
                {
                    $query->where('mobile_no', 'LIKE', '%'.$filter->mobile.'%');
                }

                if(!empty($filter->review)) // driver_total_review filter
                {
                    $driver_total_review = explode('-',$filter->review);
                    $query = $query->where(function($q) use ( $driver_total_review ){
                        $q->has('driver_total_review','>=',$driver_total_review[0]);
                        $q->has('driver_total_review','<=',$driver_total_review[1]);
                    });
                }

                if(!empty($filter->avg_rating)) // driver_avg_rating filter
                {
                    $driver_avg_rating = explode('-',$filter->avg_rating);
                    $query->whereHas('driver_avg_rating' , function ($q) use ( $driver_avg_rating ) {
                        $q->havingRaw('AVG(taxi_ratting.ratting) >= '.$driver_avg_rating[0]);
                        $q->havingRaw('AVG(taxi_ratting.ratting) <= '.$driver_avg_rating[1]);
                    });

                }

                $driver_reviews = $query->orderByRaw('taxi_users.user_id DESC')->paginate(10)->toArray();


            }
            else
            {
                return response()->json([
                    'status'    => false,
                    'message'   => 'filter parameter is required',
                    'data'    => new ArrayObject,
                ], 200);
            }

        }
        else {

            $list = 'All';
            $driver_reviews = User::select(
                DB::raw('CONCAT(first_name," ",last_name) as driver_name'),
                    'mobile_no as driver_mobile','user_id as driver_id'
            )
            ->withCount('driver_total_review')
            ->withCount([
                'driver_avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                }
            ])
            ->where('user_type',1)
            ->orderByRaw('user_id DESC')
            ->paginate(10)->toArray();

        }

        if($driver_reviews['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => $list.' Driver reviews', 
                'data'    => $driver_reviews,
            ], 200);   
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }

    }

    // view driver reviews
    public function view_driver_reviews($request)
    {
        $driver_reviews = DB::table('taxi_ratting')
            ->select('taxi_ratting.*','driver_id','rider_id',
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as rider_name'),
                'taxi_users.mobile_no as rider_mobile' 
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->join('taxi_users','taxi_request.rider_id','taxi_users.user_id')
            ->where('taxi_request.driver_id',$request->driver_id)
            ->where('review_by','rider')
            ->get();

        if($driver_reviews)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Driver reviews', 
                'data'    => $driver_reviews,
            ], 200);   
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }

    }

    // get driver online_log
    public function get_driver_online_log($request)
    {

        $driver_id = $request['driver_id'];
        $duration = $request['duration'];
        $time = $request['time'];
        $driver_online_hours = [];

        if($duration == 'Day')
        {
            $driver_online_hours = DB::table('taxi_driver_online_hours')
                ->select('*',DB::raw('TIMEDIFF(end_time,start_time) as time'))
                ->where('driver_id',$driver_id)
                ->where('created_date',$time)
                ->where('end_time','!=','00:00:00')
                ->orderByRaw('id DESC')
                ->get();
        }
        if($duration == 'Week')
        {
            $dates = explode(' ', $time);
            $driver_online_hours = DB::table('taxi_driver_online_hours')
                ->select('*',DB::raw('TIMEDIFF(end_time,start_time) as time'))
                ->where('driver_id',$driver_id)
                ->whereBetween('created_date',$dates)
                ->where('end_time','!=','00:00:00')
                ->orderByRaw('id DESC')
                ->get();

        }
        if($duration == 'Month')
        {
            $month = explode('/',$time);
            $this_month_start = date(''.$month[1].'-'.$month[0].'-01');
            $this_month_end   = date(''.$month[1].'-'.$month[0].'-t');

            $driver_online_hours = DB::table('taxi_driver_online_hours')
                ->select('*',DB::raw('TIMEDIFF(end_time,start_time) as time'))
                ->where('driver_id',$driver_id)
                ->whereBetween('created_date',[trim($this_month_start),trim($this_month_end)])
                ->where('end_time','!=','00:00:00')
                ->orderByRaw('id DESC')
                ->get();

        }
        if($duration == 'Year')
        {
            $this_year_start = date("".$time."-m-d",strtotime("first day of january this year"));
            $this_year_end   = date("".$time."-m-d",strtotime("December 31st"));

            $driver_online_hours = DB::table('taxi_driver_online_hours')
                ->select('*',DB::raw('TIMEDIFF(end_time,start_time) as time'))
                ->where('driver_id',$driver_id)
                ->whereBetween('created_date',[trim($this_year_start),trim($this_year_end)])
                ->where('end_time','!=','00:00:00')
                ->orderByRaw('id DESC')
                ->get();

        }


        if($driver_online_hours)
        {
            $Thours=0;
			$Tminutes=0;
			$Tseconds=0;

            foreach ($driver_online_hours as $driver) {
                $cal_time = 0;
                if($driver->time != '00:00:00')
                {
                    $timestm=explode(':', $driver->time);
                    
                    $Thours+=(int)$timestm[0];
                    $Tminutes+=(int)$timestm[1];
                    $Tseconds+=(int)$timestm[2];
                }
            }

            $totalSeconds= (($Thours*(60*60))+($Tminutes*60)+$Tseconds);
			$driver_online_total_hour = $this->secToHR($totalSeconds);

            return response()->json([
                'status'    => true,
                'message'   => 'Driver online log', 
                'data'    => $driver_online_hours,
                'total_hour'    => $driver_online_total_hour,
            ], 200);   
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => array(),
                'total_hour'    => 0,
            ], 200);
        }

    }


    // Sub Function =====================


    public function car_images($driver_detail_id)
    {
        $image_list = DB::table('taxi_car_images')
            ->select('image','id')
            ->where('driver_detail_id',$driver_detail_id)
            ->get();
        
        $list = [];
        foreach ($image_list as  $value) {

            $image['id'] = $value->id;
            $image['image'] = env('AWS_S3_URL').$value->image;
            $list[] = $image;
        }

        return $list;
    }

    public function delete_car_images($driver_detail_id)
    {
        $image_list = DB::table('taxi_car_images')
            ->select('image','id')
            ->where('driver_detail_id',$driver_detail_id)
            ->get();
        
        foreach ($image_list as  $value) {

            $image_path = $value->image; 
    
            // delete files
            Storage::disk('s3')->exists($image_path) ? Storage::disk('s3')->delete($image_path) : '';
            $delete = DB::table('taxi_car_images')->where('id',$value->id)->delete();
        }

    }

    public function acceptance_rejected_ratio($driver_id)
    {
        $accepted = DB::table('taxi_request')
            ->select(DB::raw('count(id) as accepted'))
            ->whereRaw('FIND_IN_SET('.$driver_id.',all_driver)')
            ->value('accepted');

        $total = DB::table('taxi_request')
            ->select(DB::raw('count(id) as total'))
            ->whereRaw('FIND_IN_SET('.$driver_id.',all_driver)')
            ->orWhereRaw('FIND_IN_SET('.$driver_id.',rejected_by)')
            ->value('total');

        $rejected =  $total - $accepted;

        if($total)
        {
            $ratio['rejected_ratio'] = round((($rejected / $total) * 100),2);
            $ratio['acceptance_ratio'] = round((($accepted / $total) * 100),2);
        }
        else
        {
            $ratio['rejected_ratio'] = 0;
            $ratio['acceptance_ratio'] = 0;
        }

        return $ratio;

    }
    
    public function secToHR($seconds) 
    {

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        
        return "$hours.$minutes";
    }

    public function total_online_hours($driver_id)
    {
        $hours = DB::table('taxi_driver_online_hours')
            ->select(DB::raw('TIMEDIFF(end_time,start_time) as time'))
            ->where('driver_id',$driver_id)
            ->where('end_time','!=','00:00:00')
            ->get();

        $total_hours = 0;
        if(is_array($hours) || is_object($hours) && !empty($hours))
        {
            $Thours=0;
            $Tminutes=0;
            $Tseconds=0;
            foreach ($hours as $hour) {

                $cal_time=0;
                $timestm=explode(':',$hour->time);
                if($timestm[0]!=00)
                {	      
                    $Thours+=(int)$timestm[0];
                }
                $Tminutes+=(int)$timestm[1];
                $Tseconds+=(int)$timestm[2];
                $totalSeconds= (($Thours*(60*60))+($Tminutes*60)+$Tseconds);
            
                $total_hours = $this->secToHR($totalSeconds);
            }
        }

        return $total_hours;
        
    } 

    public function total_online_hours_lastweek($driver_id)
    {
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week);
        $end_week = strtotime("next friday",$start_week);
        $start_week = date("Y-m-d",$start_week);
        $end_week = date("Y-m-d",$end_week);


        $hours = DB::table('taxi_driver_online_hours')
            ->select(DB::raw('TIMEDIFF(end_time,start_time) as time'))
            ->where('driver_id',$driver_id)
            ->where('end_time','!=','00:00:00')
            ->whereBetween('created_date', [$start_week, $end_week])
            ->value('time');

        $total_hours = 0;
        if(is_array($hours) || is_object($hours) && !empty($hours))
        {
            $Thours=0;
            $Tminutes=0;
            $Tseconds=0;
            foreach ($hours as $hour) {

                $cal_time=0;
                $timestm=explode(':',$hour->time);
                if($timestm[0]!=00)
                {	      
                    $Thours+=(int)$timestm[0];
                }
                $Tminutes+=(int)$timestm[1];
                $Tseconds+=(int)$timestm[2];
                $totalSeconds= (($Thours*(60*60))+($Tminutes*60)+$Tseconds);
            
                $total_hours = $this->secToHR($totalSeconds);
            }
        }

        return $total_hours;
        
    }

    public function total_online_hours_currentweek($driver_id)
    {
        //==========================================SOS 30-08-2019=============================
        $previous_week = strtotime("0 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week);
        $end_week = strtotime("next friday",$start_week);
        $start_week = date("Y-m-d",$start_week);
        $end_week = date("Y-m-d",$end_week);
        
        $hours = DB::table('taxi_driver_online_hours')
            ->select(DB::raw('TIMEDIFF(end_time,start_time) as time'))
            ->where('driver_id',$driver_id)
            ->where('end_time','!=','00:00:00')
            ->whereBetween('created_date', [$start_week, $end_week])
            ->value('time');

        $total_hours = 0;
        if(is_array($hours) || is_object($hours) && !empty($hours))
        {
            $Thours=0;
            $Tminutes=0;
            $Tseconds=0;
            foreach ($hours as $hour) {

                $cal_time=0;
                $timestm=explode(':',$hour->time);
                if($timestm[0]!=00)
                {	      
                    $Thours+=(int)$timestm[0];
                }
                $Tminutes+=(int)$timestm[1];
                $Tseconds+=(int)$timestm[2];
                $totalSeconds= (($Thours*(60*60))+($Tminutes*60)+$Tseconds);
            
                $total_hours = $this->secToHR($totalSeconds);
            }
        }

        return $total_hours;

    }

    // get Driver Ratting Review Data
    public function getDriverRatRevData($driver_id)
    {
        $ratting_data = Ratting::select('taxi_ratting.*','driver_id','rider_id')
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->where('taxi_request.driver_id',$driver_id)
            ->where('taxi_ratting.review_by','rider')
            ->get();
        if($ratting_data)
        {
            $data = [];
            foreach($ratting_data as $ratting)
            {

                $user_data = User::where('user_id',$ratting['rider_id'])->first();
                if($user_data)
                {
                    $ratting['user_name'] = $user_data->first_name." ".$user_data->last_name;
                    $ratting['profile_pic'] = $user_data->profile_pic != ''? env('AWS_S3_URL').$user_data->profile_pic : '';
                }
                else
                {
                    $ratting['user_name'] = 'Anonymous User';
                    $ratting['profile_pic'] = '';
                }

                $data[] = $ratting;
            }

            return $data;

        }
        else
        {
            return array();
        }
    }

    public function driver_ratting_review($driver_id)
    {
        $ratting_review = DB::table('taxi_ratting')->select(
                DB::raw('count(taxi_ratting.id) as total_review, ROUND(coalesce(avg(ratting),0),1) as avg_ratting')
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->where('taxi_request.driver_id',$driver_id)
            ->where('taxi_ratting.review_by','rider')
            ->first();

        return $ratting_review;

    }

    public function sentNotificationOnVerified($msg,$device_token,$type, $device_type)
    {	
        $session_user = $this->qb_create_session_with_user();
        $session_data = json_decode($session_user);
        $token = $session_data->session->token;

                $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        if ($device_type == 'A')
        {
            $fields = array(
                'to' => $device_token,
                'data' => array('message' => "success" ,'type' => $type,'title' => $msg)
            );
            $headers = array(
                'Authorization:key=AIzaSyBHZX8zi36hoodNoZLjrZxbgtTV9OwoyPw',
                'Content-Type:application/json'
            );		
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            $data = json_encode($fields['data']);
            return json_encode($result).json_encode($fields);
        }
        else if ($device_type == 'I')
        {
            $apnsServer = 'ssl://gateway.push.apple.com:2195';
            //$apnsServer = 'ssl://gateway.sandbox.push.apple.com:2195';
            $privateKeyPassword = '1';
            $deviceToken =$device_token;
            $pushCertAndKeyPemFile = 'pushcert.pem';
            $stream = stream_context_create();
            stream_context_set_option($stream,'ssl','passphrase',$privateKeyPassword);
            stream_context_set_option($stream,'ssl','local_cert',$pushCertAndKeyPemFile);

            $connectionTimeout = 20;
            $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $connection = stream_socket_client($apnsServer,$errorNumber,$errorString,$connectionTimeout,$connectionType,$stream);
            if (!$connection){
                // echo 0;
                // exit;
            } else {
                // echo 1;
            }
            $messageBody['aps'] = array(
                    'alert' => array(
                        'title' => "Notification from Taxisti",
                        'body' => $msg
                    ),
                    "type" => 'admin_notification',
                    "badge" => +1,
                    "sound" => 'default',
                );
            $payload = json_encode($messageBody);
            $notification = chr(0) .
            pack('n', 32) .
            pack('H*', $deviceToken) .
            pack('n', strlen($payload)) .
            $payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            fclose($connection);
            if (!$wroteSuccessfully){
                return 0;
            }
            else {
                return 1;
            }

        }
    }

    public function qb_create_session_with_user()
    {
        DEFINE('APPLICATION_ID', 69589);
        DEFINE('AUTH_KEY', "YKnMcUtfn792W-e");
        DEFINE('AUTH_SECRET', "fUDgC4R4qmGzwNr");

        // User credentials
        DEFINE('USER_LOGIN', "Taxisti");                  // Your Project Name in QuickBlox
        DEFINE('USER_PASSWORD', "Taxisti2016libya");      // QuickBlox Password

        // Quickblox endpoints
        DEFINE('QB_API_ENDPOINT', "https://api.quickblox.com");
        DEFINE('QB_PATH_SESSION', "session.json");

        // Generate signature
        $nonce = rand();
        $timestamp = time(); // time() method must return current timestamp in UTC but seems like hi is return timestamp in current time zone
        $signature_string = "application_id=".APPLICATION_ID."&auth_key=".AUTH_KEY."&nonce=".$nonce."&timestamp=".$timestamp."&user[login]=".USER_LOGIN."&user[password]=".USER_PASSWORD;

        $signature = hash_hmac('sha1', $signature_string , AUTH_SECRET);

        // Build post body
        $post_body = http_build_query(array(
                        'application_id' => APPLICATION_ID,
                        'auth_key' => AUTH_KEY,
                        'timestamp' => $timestamp,
                        'nonce' => $nonce,
                        'signature' => $signature,
                        'user[login]' => USER_LOGIN,
                        'user[password]' => USER_PASSWORD
                        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, QB_API_ENDPOINT . '/' . QB_PATH_SESSION); // Full path is - https://api.quickblox.com/session.json
        curl_setopt($curl, CURLOPT_POST, true); // Use POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body); // Setup post body
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Receive server response

        // Execute request and read responce
        $responce = curl_exec($curl);

        // Check errors
        if ($responce) {
                // echo $responce . "\n";
        } else {
                $error = curl_error($curl). '(' .curl_errno($curl). ')';
                echo $error . "\n";
        }

        // Close connection
        curl_close($curl);

        return $responce;
    }

    // store notification
    public function store_notification($user_id,$type,$msg)
    {

        $input['type'] = $type;
        $input['message'] = json_encode($msg);
        $input['user_id'] = $user_id;
        $input['datetime'] = date('Y-m-d H:i:s');

        DB::table('taxi_notification')->insert($input);
        return true;
    }

}   