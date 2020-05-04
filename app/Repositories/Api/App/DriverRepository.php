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