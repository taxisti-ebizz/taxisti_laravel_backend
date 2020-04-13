<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\GetPendingRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetRunningRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetCompletedRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetNoResponseRideListRequest;

class RideController extends Controller
{
    protected $ride;

    public function __construct()
    {
        $this->ride = new RideRepository;
    }

    // get pending ride list
    public function get_pending_ride_list(GetPendingRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_pending_ride_list($request);
    }

    // get running ride list
    public function get_running_ride_list(GetRunningRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_running_ride_list($request);
    }

    // get completed ride list
    public function get_completed_ride_list(GetCompletedRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_completed_ride_list($request);
    }

    // get no response ride list
    public function get_no_response_ride_list(GetNoResponseRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_no_response_ride_list($request);
    }

}
