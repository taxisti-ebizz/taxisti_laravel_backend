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

class DriverRepository extends Controller
{

    protected $appCommon;

    public function __construct()
    {
        $this->appCommon = new AppCommonRepository;
    }
    
    // delete driver car image
    public function delete_car_image($request ,$id)
    {
        $image = DB::table('taxi_car_images')->where('id',$id)->first();

        $car_image_path = $image->image; 

        // delete files
        Storage::disk('s3')->exists($car_image_path) ? Storage::disk('s3')->delete($car_image_path) : '';

        DB::table('taxi_car_images')->where('id',$id)->delete();

        $image_list = $this->appCommon->car_images($image->driver_detail_id);

        return response()->json([
            'status'    => true,
            'message'   => 'Car image deleted', 
            'data'    => $image_list,
        ], 200);   
    }

    // get driver car image
    public function get_car_image($request)
    {
        $user_id = Auth()->user()->user_id;

        $driver = Driver::where('driver_id',$user_id)->first();

        if($driver)
        {
            $car_image = $this->appCommon->car_images($driver->id);

            return response()->json([
                'status'    => true,
                'message'   => 'Car Image', 
                'data'    => $car_image,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data found', 
                'data'    => array(),
            ], 200);
        }
    }

    // get driver status
    public function get_driver_status($request)
    {
        $driver_id = Auth()->user()->user_id;

        $driver = User::where('user_type',1)->where('user_id',$driver_id)->first();

        if($driver)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Success', 
                'data'    => $driver['verify'],
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'Driver not exist', 
                'data'    => array(),
            ], 200);
        }
    }

    // driver detail
    public function driver_detail($request)
    {
        $user_id = Auth()->user()->user_id;

        if($request['type'] =='add_driver')
        {

            $check_driver = Driver::where('driver_id',$user_id)->first();

            if(!$check_driver)
            {
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

                $driver['driver_id'] = $user_id;
                $driver['car_brand'] = $request['car_brand'] != '' ? $request['car_brand'] : "";
                $driver['car_year'] = $request['car_year'] != '' ? $request['car_year'] : "";
                $driver['plate_no'] = $request['plate_no'] != '' ? $request['plate_no'] : "";
                $driver['availability'] = $request['availability'] != "" ? $request['availability'] : 0;
                $driver['current_location'] = $request['current_location'] !="" ? $request['current_location'] : "";
                $driver['latitude']=$request['lat'] !="" ? $request['lat'] : "";
                $driver['longitude']=$request['long'] !="" ? $request['long'] : "";
                $driver['car_pic'] = '';
                $driver['created_datetime'] = date('Y-m-d h:i:s');
        
                $add_driver_detail = Driver::insert($driver);
                
                if($add_driver_detail)
                {
                    
                    $input['user_type'] = 1;
                    $input['status'] = 0;
                    $input['updated_date'] = date('Y-m-d h:i:s');
                    isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                    isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                    isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';
                    isset($request['password']) ? $input['password'] = md5($request['password']) : '';

                    $update_user = User::where('user_id',$user_id)->update($input);

                    $driver_data = $this->appCommon->get_driver($user_id);
                    $driver_data['car_images'] = $this->appCommon->car_images($driver_data['driver_detail']['id']);
                    
                    $msg['message']="Driver Details Added Successfully.";
                    $msg['message_ar'] = "تم إضافة معلومات السائق بنجاح";
                    $msg['status']=1;
                    $msg['data'] = $driver_data;

                }
                else{
        
                    $msg['message']="Failed.";
                    $msg['status']=2;
                }
                
            }
            else{
                $msg['message']="User Allready Exists.";
                $msg['message_ar'] = "تم تسجيل السائق مسبقا";
                $msg['status']=2;
            }
        
        }
        elseif($request['type']=='add_car_images')
        {
            
            if(isset($request['driver_detail_id']) && $request['driver_detail_id']!='')
            {
                // car_image handling 
                if($request->file('car_pic'))
                {
                    
                    foreach ($request->file('car_pic') as  $car_image) {
    
                        // $car_image = $request->file('car_image');
                        $imageName = 'uploads/car_images/'.time().'.'.$car_image->getClientOriginalExtension();
                        $img = Storage::disk('s3')->put($imageName, file_get_contents($car_image), 'public');
    
                        $car['driver_detail_id'] = $request['driver_detail_id'];  
                        $car['image'] = $imageName;  
                        $car['datetime'] = date('Y-m-d H:i:s');  
                        $car_image = DB::table('taxi_car_images')->insert($car);
                    }

                    $driver_data = $this->appCommon->get_driver($user_id);
                    $driver_data['car_images'] = $this->appCommon->car_images($driver_data['driver_detail']['id']);

                    $msg['message']="Car Image Added Successfully.";
                    $msg['message_ar'] = "تم تحميل صورة السيارة بنجاح";
                    $msg['status']=1;
                    $msg['data'] = $driver_data;
        
                }
                else
                {
                    $msg['message']="Failed.";
                    $msg['status']=2;
                }
                
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        elseif($request['type']=='edit_driver')
        {
            $driver_detail = Driver::where('driver_id',$user_id)->first();

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

            isset($user_id) ? $driver['driver_id'] = $user_id : '';
            isset($request['car_brand']) ? $driver['car_brand'] = $request['car_brand'] : '';
            isset($request['car_year']) ? $driver['car_year'] = $request['car_year'] : '';
            isset($request['plate_no']) ? $driver['plate_no'] = $request['plate_no'] : '';
            isset($request['availability']) ? $driver['availability'] = $request['availability'] : '';
            isset($request['current_location']) ? $driver['current_location'] = $request['current_location'] : '';
            isset($request['lat']) ? $driver['latitude'] = $request['lat'] : '';
            isset($request['long']) ? $driver['longitude'] = $request['long'] : '';
            $driver['last_update'] = date('Y-m-d h:i:s');
    
            $update_driver_detail = Driver::where('driver_id',$user_id)->update($driver);
            
            if($update_driver_detail)
            {
                
                $input['user_type'] = 1;
                $input['status'] = 0;
                $input['updated_date'] = date('Y-m-d h:i:s');
                isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';

                $update_user = User::where('user_id',$user_id)->update($input);

                $driver_data = $this->appCommon->get_driver($user_id);
                $driver_data['car_images'] = $this->appCommon->car_images($driver_data['driver_detail']['id']);
                
                $msg['message']="Driver Details Updated.";
                $msg['message_ar'] = "تم تحميل معلومات السائق بنجاح";
                $msg['status']=1;
                $msg['data'] = $driver_data;
    

            }
            else{
    
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        
        }
        elseif($request['type']=='get_driver')
        {
            $driver = DB::table('taxi_driver_detail')
            ->select('taxi_driver_detail.*','taxi_users.*')
            ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
            ->where('taxi_driver_detail.driver_id',$user_id)
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->appCommon->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->appCommon->car_images($driver->id);

                $msg['status']=1;
                $msg['message']="Success";
                $msg['data']=$driver;
        
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        elseif($request['type']=='get_driver_all_detail')
        {
            $driver = DB::table('taxi_driver_detail')
            ->select('taxi_driver_detail.*','taxi_users.*')
            ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
            ->where('taxi_driver_detail.driver_id',$user_id)
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->appCommon->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->appCommon->car_images($driver->id);

                $msg['status']=1;
                $msg['message']="Success";
                $msg['data']=$driver;
        
            }
            else
            {
                $msg['message']="Failed.";
                $msg['status']=2;
            }
        }
        
        return response()->json($msg, 200);

    }

    // request_action
    public function request_action($request)
    {
        $driver_id = Auth()->user()->user_id;
        $request_data = Request::where('id',$request['request_id'])->first();

        if($request['status'] == 1)
        {
            if($request_data->status == 0)
            {
                $update['status'] = 1;
                $update['updated_date'] = date('Y-m-d H:i:s');
                $update_status = Request::where('id',$request['request_id'])->update($update);
                $rider = $this->appCommon->get_rider($request_data['rider_id']);

                $msg['status']=true;
                $msg['message']='Success';
                $msg['data'] = $rider;
            }
            elseif($request['status']!=0)
            {
                $msg['status']=false;
                $msg['message']='Action already taken for this Request';
            }
        }
        elseif($request['status'] == 2)
        {          
            if($request_data['status']==0)
            {

                $rider = $this->appCommon->get_rider($request_data['rider_id']);
                $msg['data'] = $rider;
                if($request_data['all_driver']!='')
                {
                    $cur_driver_id = $request_data['driver_id'];
                    $cur_all_driver_id = explode(",",$request_data['all_driver']);
                    $cur_rej_driver_id = array();
                    if(strpos($request_data['rejected_by'],","))
                    {
                        $cur_rej_driver_id = explode(",",$request_data['rejected_by']);
                    }
                    else{
                        $cur_rej_driver_id[] = $request_data['rejected_by'];
                    }

                    $new_all_driver=array();
                    foreach($cur_all_driver_id as $cad)
                    {
                        if($cur_driver_id!=$cad)
                        {
                            $new_all_driver[]=$cad;
                        }
                        else
                        {
                            $cur_rej_driver_id[]=$cur_driver_id;
                        }
                    }
                    
                    if(empty($cur_rej_driver_id))
                    {
                        $cur_rej_driver_id[]=$cur_driver_id;
                    }
                    
                    $not_avlbl_drive_ids = [];
                    $new_driver = '';
                    foreach ($new_all_driver as $new_driver_id) 
                    {
                        if ($this->appCommon->check_driver_availablity($new_driver_id))
                        {
                            $new_driver=$new_driver_id;
                            break;
                        }
                        else 
                        {
                            $not_avlbl_drive_ids[]  = $new_driver_id;
                            $cur_rej_driver_id[] = $new_driver_id;   
                        }
                    }
                    
                    if (!empty($not_avlbl_drive_ids))
                    {
                        $new_all_driver = array_diff($new_all_driver, $not_avlbl_drive_ids);
                    }

                    $new_all_d='';
                    foreach($new_all_driver as $nad)
                    {

                        if($new_all_d!='')
                        {
                            $new_all_d.=',';
                        }
                        $new_all_d.=$nad;
                    }

                    $new_rej='';
                    foreach($cur_rej_driver_id as $crd)
                    {
                        if($new_rej!='')
                        {
                            $new_rej.=',';
                        }
                        $new_rej.=$crd;
                    }

                    if($new_driver!='')
                    {
                        $update['status'] = 0;
                        $update['driver_id'] = $new_driver;
                        $update['rejected_by'] = $new_rej;
                        $update['all_driver'] = $new_all_d;
                        $update['updated_date'] = date('Y-m-d H:i:s');
                        $update_status = Request::where('id',$request['request_id'])->update($update);
        

                        $driver_data = $this->appCommon->get_driver($driver_id);
                        $this->appCommon->send_request_notification_to_driver($driver_data['device_token'],$request['request_id'],$new_driver,$request_data['rider_id'],$driver_data['device_type']);

                        $msg['status']=true;
                        $msg['message']='Request submitted Successfully';		

                    }
                    else
                    {
                        $update['status'] = 2;
                        $update['rejected_by'] = $new_rej;
                        $update['all_driver'] = $new_all_d;
                        $update['updated_date'] = date('Y-m-d H:i:s');
                        $update_status = Request::where('id',$request['request_id'])->update($update);

                        $msg['status']=true;
                        $msg['message']='Request submitted Successfully';	
                    }
                }
                else{

                    //update request status =2

                    /*$msg['notification']=sendreqnotitouser_onreject($request_data['device_token'],$request_data['id'],$con,$request_data['rider_id']);*/


                    $msg['status']=true;
                    $msg['message']='Success.';

                }
            }else{
                $msg['status']=false;
                $msg['message']='Action already taken for this Request';
            }
        }
        elseif($request['status'] == 3)
        {
            $update['status'] = 3;
            $update['end_datetime'] = date('Y-m-d H:i:s');
            $update['updated_date'] = date('Y-m-d H:i:s');
            $update_status = Request::where('id',$request['request_id'])->update($update);


            if(isset($driver_id) && $driver_id!='')
            {
    
                $type='driver';
    
                $driver_data = $this->appCommon->get_driver($driver_id);
                $rider_data = $this->appCommon->get_rider($request_data['rider_id']);
      
                $msg['rider'] = $rider_data;
                $msg['driver'] = $driver_data;
        
            }
            if(isset($request['rider_id']) && $request['rider_id']!='')
            {   
    
                $type='rider';

                $update['cancel_by'] = 2;
                $update['end_datetime'] = date('Y-m-d H:i:s');
                $update['updated_date'] = date('Y-m-d H:i:s');
                $update_status = Request::where('id',$request['request_id'])->update($update);
       
                $driver_data = $this->appCommon->get_driver($request_data['driver_id']);
                $rider_data = $this->appCommon->get_rider($request['rider_id']);

                $msg['driver'] = $driver_data; 
                $msg['rider'] = $rider_data; 
    
            }
            $msg['status']=true;
            $msg['message']='Success.';
        }
        else
        {
            $msg['status']=false;
            $msg['message']='Failed';
        }

        return response()->json($msg, 200);
    }




}