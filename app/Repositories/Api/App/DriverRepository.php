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

class DriverRepository extends Controller
{
    
    // delete driver car image
    public function delete_car_image($request ,$id)
    {
        $image = DB::table('taxi_car_images')->where('id',$id)->first();

        $car_image_path = $image->image; 

        // delete files
        Storage::disk('s3')->exists($car_image_path) ? Storage::disk('s3')->delete($car_image_path) : '';

        DB::table('taxi_car_images')->where('id',$id)->delete();

        $image_list = $this->car_images($image->driver_detail_id);

        return response()->json([
            'status'    => true,
            'message'   => 'Car image deleted', 
            'data'    => $image_list,
        ], 200);   
    }

    // get driver car image
    public function get_car_image($request)
    {
        $driver = Driver::where('driver_id',$request['user_id'])->first();

        if($driver)
        {
            $car_image = $this->car_images($driver->id);

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
        $driver = User::where('user_type',1)->where('user_id',$request['driver_id'])->first();

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
        if($request['type'] =='add_driver')
        {

            $check_driver = Driver::where('driver_id',$request['user_id'])->first();

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

                $driver['driver_id'] = $request['user_id'];
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

                    $update_user = User::where('user_id',$request['user_id'])->update($input);
                     
                    $msg['message']="Driver Details Added Successfully.";
                    $msg['message_ar'] = "تم إضافة معلومات السائق بنجاح";
                    $msg['status']=1;

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
                        $car['datetime'] = date('Y-m-d H:m:s');  
                        $car_image = DB::table('taxi_car_images')->insert($car);
                    }

                    $msg['message']="Car Image Added Successfully.";
                    $msg['message_ar'] = "تم تحميل صورة السيارة بنجاح";
                    $msg['status']=1;
        
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
            $driver_detail = Driver::where('driver_id',$request['user_id'])->first();

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

            isset($request['user_id']) ? $driver['driver_id'] = $request['user_id'] : '';
            isset($request['car_brand']) ? $driver['car_brand'] = $request['car_brand'] : '';
            isset($request['car_year']) ? $driver['car_year'] = $request['car_year'] : '';
            isset($request['plate_no']) ? $driver['plate_no'] = $request['plate_no'] : '';
            isset($request['availability']) ? $driver['availability'] = $request['availability'] : '';
            isset($request['current_location']) ? $driver['current_location'] = $request['current_location'] : '';
            isset($request['lat']) ? $driver['latitude'] = $request['lat'] : '';
            isset($request['long']) ? $driver['longitude'] = $request['long'] : '';
            $driver['last_update'] = date('Y-m-d h:i:s');
    
            $update_driver_detail = Driver::where('driver_id',$request['user_id'])->update($driver);
            
            if($update_driver_detail)
            {
                
                $input['user_type'] = 1;
                $input['status'] = 0;
                $input['updated_date'] = date('Y-m-d h:i:s');
                isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';

                $update_user = User::where('user_id',$request['user_id'])->update($input);
                    
                $msg['message']="Driver Details Updated.";
                $msg['message_ar'] = "تم تحميل معلومات السائق بنجاح";
                $msg['status']=1;
    

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
            ->where('taxi_driver_detail.driver_id',$request['user_id'])
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->car_images($driver->id);

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
            ->where('taxi_driver_detail.driver_id',$request['user_id'])
            ->first();
    
            if($driver)
            {
                $driver->licence = $driver->licence != ''? env('AWS_S3_URL').$driver->licence : '';
                $driver->profile = $driver->profile != ''? env('AWS_S3_URL').$driver->profile : '';
                $driver->profile_pic = $driver->profile_pic != ''? env('AWS_S3_URL').$driver->profile_pic : '';
                
                // add ratting
                $driver->ratting = $this->get_driver_ratting($driver->driver_id);   
                // add car images
                $driver->car_images = $this->car_images($driver->id);

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







    
    // Sub Function =====================


    // get driver car image
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

    // get driver 
    public function get_driver($driver_id)
    {
        $driver  = User::where('user_id',$driver_id)->first();
        $driver['ratting'] = $this->get_driver_ratting($driver_id);
        $driver['driver_detail'] = $this->get_driver_detail($driver_id);

        return $driver; 
    }

    // get driver detail
    public function get_driver_detail($driver_id)
    {
        $driver_detail = Driver::where('driver_id',$driver_id)->first();

        if($driver_detail)
        {
            $driver_detail['profile'] = $driver_detail['profile'] != '' ? env('AWS_S3_URL').$driver_detail['profile'] : '';
            $driver_detail['licence'] = $driver_detail['licence'] != '' ? env('AWS_S3_URL').$driver_detail['licence'] : '';

        }
        else {
            $driver_detail = array();
        }

        return $driver_detail;

    }

    // get driver ratting
    public function get_driver_ratting($driver_id)
    {
        $ratting = Ratting::select(
            DB::raw('coalesce(AVG(ratting),0) as avgrating, count(review) as countreview'))
        ->where('review_by','rider')
        ->where('driver_id',$driver_id)->first();

        return $ratting;
    }

    
}