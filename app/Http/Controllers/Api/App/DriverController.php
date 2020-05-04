<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\DriverRepository;
use App\Http\Requests\Api\App\Driver\GetCarImageRequest;
use App\Http\Requests\Api\App\Driver\DeleteCarImageRequest;

class DriverController extends Controller
{
    protected $driver;

    public function __construct()
    {
        $this->driver = new DriverRepository;
    }

    // delete car image
    public function delete_car_image(DeleteCarImageRequest $request, $id)
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

    // get car image
    public function get_car_image(GetCarImageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->get_car_image($request);

    }

}
