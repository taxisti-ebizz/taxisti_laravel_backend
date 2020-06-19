<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\RiderRepository;
use App\Http\Requests\Api\App\Rider\GetDriverRequest;
use App\Http\Requests\Api\App\Rider\RequestRideRequest;
use App\Http\Requests\Api\App\Rider\GetDriverDetailRequest;

class RiderController extends Controller
{
    protected $rider;

    public function __construct()
    {
        $this->rider = new RiderRepository;
    }

    // get driver 
    protected function get_driver(GetDriverRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->rider->get_driver($request);

    }

    // request ride 
    protected function request_ride(RequestRideRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->rider->request_ride($request);
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

        return $this->rider->get_driver_detail($request);

    }

}
