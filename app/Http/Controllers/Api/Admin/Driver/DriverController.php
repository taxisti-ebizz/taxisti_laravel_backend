<?php

namespace App\Http\Controllers\Api\Admin\Driver;

use App\Models\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\DriverRepository;
use App\Http\Requests\Api\Admin\Driver\DeleteDriverRequest;
use App\Http\Requests\Api\Admin\Driver\GetDriverListRequest;
use App\Http\Requests\Api\Admin\Driver\DeleteCarImageRequest;
use App\Http\Requests\Api\Admin\Driver\GetDriverDetailRequest;
use App\Http\Requests\Api\Admin\Driver\EditDriverDetailRequest;
use App\Http\Requests\Api\Admin\Driver\EditDriverStatusRequest;


class DriverController extends Controller
{
    protected $driver;

    public function __construct()
    {
        $this->driver = new DriverRepository;
    }

    // get driver list
    protected function get_driver_list(GetDriverListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->get_driver_list($request);
    }

    // get driver detail
    protected function get_driver_detail(GetDriverDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->get_driver_detail($request);

    }
    
    // edit driver detail
    protected function edit_driver_detail(EditDriverDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->edit_driver_detail($request);

    }

    // edit driver status
    protected function edit_driver_status(EditDriverStatusRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->edit_driver_status($request);

    }

    // delete driver
    protected function delete_driver(DeleteDriverRequest $request, $driver_id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->delete_driver($request,$driver_id);
    }

    // delete car image
    protected function delete_car_image(DeleteCarImageRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->delete_car_image($request, $id);
    }
    


}
