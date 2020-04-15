<?php


namespace App\Repositories\Api\Admin;

use File;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class DriverRepository extends Controller
{

    // get driver list
    public function get_driver_list($request)
    {
        $driver_list = array();
        if($request['type'] == 'current') {
            // current driver
            $list = 'Current';
        
            $driver_list = Driver::select('taxi_driver_detail.*','taxi_users.*')
                ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
                ->withCount([
                    'driver_rides' => function ($query) {
                        $query->where('is_canceled',0);
                    }])
                ->withCount([
                    'driver_cancel_ride' => function ($query) {
                        $query->where('is_canceled',1);
                        $query->where('cancel_by',1);
                    }])
                ->withCount([
                    'driver_total_review' => function ($query) {
                        $query->where('review_by','=','rider');
                    }
                ])
                ->withCount([
                    'driver_avg_rating' => function ($query) {
                        $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                        $query->where('review_by','=','rider');
                    }
                ])
                ->orderByRaw('taxi_users.user_id DESC')
                ->paginate(10)->toArray();
        }
        elseif($request['type'] == 'online')
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
            ->withCount([
                'driver_rides' => function ($query) {
                    $query->where('is_canceled',0);
                }])
            ->withCount([
                'driver_cancel_ride' => function ($query) {
                    $query->where('is_canceled',1);
                    $query->where('cancel_by',1);
                }])
            ->withCount([
                'driver_total_review' => function ($query) {
                    $query->where('review_by','=','rider');
                }
            ])
            ->withCount([
                'driver_avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                    $query->where('review_by','=','rider');
                }
            ])
            ->orderByRaw('taxi_users.user_id DESC')
            ->paginate(10)->toArray();

        }
        else {
            // all driver
            $list = 'All';
            $driver_list = User::select('taxi_driver_detail.*','taxi_users.*')
                ->leftJoin('taxi_driver_detail','taxi_users.user_id' , '=', 'taxi_driver_detail.driver_id')
                ->where('taxi_users.user_type',1)
                ->withCount([
                    'driver_rides' => function ($query) {
                        $query->where('is_canceled',0);
                    }])
                ->withCount([
                    'driver_cancel_ride' => function ($query) {
                        $query->where('is_canceled',1);
                        $query->where('cancel_by',1);
                    }])
                ->withCount([
                    'driver_total_review' => function ($query) {
                        $query->where('review_by','=','rider');
                    }
                ])
                ->withCount([
                    'driver_avg_rating' => function ($query) {
                        $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                        $query->where('review_by','=','rider');
                    }
                ])
                ->orderByRaw('taxi_users.user_id DESC')
                ->paginate(10)->toArray();
        }

        if($driver_list['data'])
        {
            foreach($driver_list['data'] as $driver)
            {
                // add calculation
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
    
                $data[] = $driver;
    
            }
            $driver_list['data'] = $data; 
    
            return response()->json([
                'status'    => true,
                'message'   =>  $list.' driver list', 
                'data'    => $driver_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }
    }

    // get driver detail
    public function get_driver_detail($request)
    {
        $driver = DB::table('taxi_driver_detail')
        ->select('taxi_driver_detail.*','taxi_users.*')
        ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
        ->where('taxi_users.user_type',1)
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
        $input = $request->except(['driver_id']);
        $input['updated_date'] = date('Y-m-d H:m:s');

        // profile_pic handling 
        if($request->file('profile_pic')){

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/driver_images/'.time().'.'.$profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;

            // update in driver_detail table
            $driver['profile'] = $imageName;
            $driver['last_update'] = date('Y-m-d H:m:s');
            Driver::where('driver_id',$request['driver_id'])->update($driver);
                                  
        }

        // update data
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
        $input['updated_date'] = date('Y-m-d H:m:s');

        // update status
        User::where('user_id',$request['driver_id'])->update($input);
        
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

        Driver::where('driver_id',$driver_id)->delete();
        User::where('user_id',$driver_id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'driver deleted', 
            'data'    => '',
        ], 200);   
    }

    // get driver reviews
    public function get_driver_reviews($request)
    {
        $driver_reviews = User::select(
                DB::raw('CONCAT(first_name," ",last_name) as driver_name'),
                    'mobile_no as driver_mobile','user_id as driver_id'
            )
            ->withCount([
                'driver_total_review' => function ($query) {
                    $query->where('review_by','=','rider');
                }
            ])
            ->withCount([
                'driver_avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                    $query->where('review_by','=','rider');
                }
            ])
            ->where('user_type',1)
            ->orderByRaw('user_id DESC')
            ->paginate(10)->toArray();

        if($driver_reviews['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Driver reviews', 
                'data'    => $driver_reviews,
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

    // view driver reviews
    public function view_driver_reviews($request)
    {
        $driver_reviews = DB::table('taxi_ratting')
            ->select('taxi_ratting.*',
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as rider_name'),
                'taxi_users.mobile_no as rider_mobile' 
            )
            ->join('taxi_users','taxi_ratting.rider_id','taxi_users.user_id')
            ->where('taxi_ratting.driver_id',$request->driver_id)
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
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }

    }


    // Sub Function =====================


    public function car_images($driver_detail_id)
    {
        $image_list = DB::table('taxi_car_images')
            ->select('image')
            ->where('driver_detail_id',$driver_detail_id)
            ->get();
        
        $list = [];
        foreach ($image_list as  $value) {

            $list[] = env('AWS_S3_URL').$value->image;
        }

        return $list;
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
            ->whereRaw('FIND_IN_SET('.$driver_id.',rejected_by)')
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


}   