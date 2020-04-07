<?php


namespace App\Repositories\Api\Admin;

use File;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DriverRepository extends Controller
{

    /**
     * Create a new driver instance after a valid registration.
     *
     * @param  array  $request
     * @return \App\Models\Driver
     */
    public function get_driver_list($request)
    {
        $driver_list = DB::table('taxi_driver_detail')
        ->select('taxi_driver_detail.*','taxi_users.*')
        ->join('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
        ->paginate(15)->toArray();


        // add base url 
        foreach($driver_list['data'] as $driver)
        {
            $driver->licence = $driver->licence != ''? url($driver->licence) : '';
            $driver->profile = $driver->profile != ''? url($driver->profile) : '';
            $driver->profile_pic = $driver->profile_pic != ''? url($driver->profile_pic) : '';

            $data[] = $driver;

        }
        $driver_list['data'] = $data; 

        return response()->json([
            'status'    => true,
            'message'   => 'All driver list', 
            'data'    => $driver_list,
        ], 200);
    }

    // get driver detail
    public function get_driver_detail($request)
    {
        $driver = Driver::where('driver_id',$request->driver_id)->first();
        if($driver)
        {
            $driver['profile_pic'] = $driver['profile_pic'] != ''? url($driver['profile_pic']) : '';
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
            $fileName = 'public/uploads/drivers/'.time().'.'.$profile_pic->getClientOriginalExtension();  
            
            // move profile_pic in destination
            if($profile_pic->move(public_path('/uploads/drivers/'), $fileName))
            {
                $input['profile_pic'] = $fileName;
            }
            else {
                return response()->json([
                    'status'    => false,
                    'message'   => 'fail to move profile_pic', 
                    'error'    => '',
                ], 200);
            }
                                  
        }

        // update data
        Driver::where('driver_id',$request['driver_id'])->update($input);
        
        // get driver 
        $driver = Driver::where('driver_id',$request->driver_id)->get()->first();
        $driver['profile_pic'] = $driver['profile_pic'] != ''? url($driver['profile_pic']) : '';


        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $driver,
        ], 200);
        
    }

    // delete driver
    public function delete_driver($request ,$driver_id)
    {
        $driver = Driver::where('driver_id',$driver_id)->first();
        $image_path = $driver['profile_pic']; 

        // delete profile_pic
        if(File::exists($image_path))
        {
            File::delete($image_path);
        }

        Driver::where('driver_id',$driver_id)->delete();
        
        return response()->json([
            'status'    => true,
            'message'   => 'driver deleted', 
            'data'    => '',
        ], 200);   
    }

}   