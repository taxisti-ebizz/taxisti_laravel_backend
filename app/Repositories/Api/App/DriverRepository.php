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

            $check_driver = Driver::find($request['user_id']);
            if($check_driver)
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
        
                if($driver_id!='' && $licence!='')
                {
                    
                    $add_driver_detail = Driver::insert($driver);
                    
                        $msg['data']['driver_detail_id']=$lst_id;
                        $qry_get_driver_details=$con->query("select * from taxi_driver_detail where id=".$lst_id);
                        if($qry_get_driver_details->num_rows>0)
                        {

                            $input['user_type'] = 1;
                            $input['status'] = 0;
                            isset($request['dob']) ? $input['date_of_birth'] = $request['dob'] : '';
                            isset($request['first_name']) ? $input['first_name'] = $request['first_name'] : '';
                            isset($request['last_name']) ? $input['last_name'] = $request['last_name'] : '';
                            isset($request['password']) ? $input['password'] = md5($request['password']) : '';

        
                            $up_qry.="where user_id=".$driver_id;
        
                            
        
                            $qry_update_user=$con->query($up_qry);
                            
                            $row_driver=$qry_get_driver_details->fetch_assoc();
                            
                            $qry_user=$con->query("select * from taxi_users where user_id=".$driver_id);
                            
                            if($qry_user->num_rows>0)
                            {
                                $row_user=$qry_user->fetch_assoc();
                                $row_driver['dob']=$row_user['date_of_birth'];
                                $row_driver['first_name']=$row_user['first_name'];
                                $row_driver['last_name']=$row_user['last_name'];
                                $row_driver['driver_ratting']=(string)round($driver_ratting['avgrating'],1);
        
                            }
        
                            if($row_driver['licence']!='')
                            {
                                $row_driver['licence']=$server_path.$row_driver['licence'];
                            }
        
                            if($row_driver['profile']!='')
                            {
                                $row_driver['profile']=$server_path.$row_driver['profile'];					
                            }
                            $msg['data']=$row_driver;
        
                        }
                        
                        
        
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