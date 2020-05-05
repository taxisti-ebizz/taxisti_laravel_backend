<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\DeleteRideRequest;
use App\Http\Requests\Api\Admin\Ride\GetFakeRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetRideAreaListRequest;
use App\Http\Requests\Api\Admin\Ride\AddAreaBoundariesRequest;
use App\Http\Requests\Api\Admin\Ride\GetPendingRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetRunningRideListRequest;
use App\Http\Requests\Api\Admin\Ride\ViewAreaBoundariesRequest;
use App\Http\Requests\Api\Admin\Ride\GetCanceledRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetCompletedRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetNoResponseRideListRequest;
use App\Http\Requests\Api\Admin\Ride\GetNoDriverAvailableListRequest;

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

    // get canceled ride list
    public function get_canceled_ride_list(GetCanceledRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_canceled_ride_list($request);
    }

    // get no driver available list
    public function get_no_driver_available_list(GetNoDriverAvailableListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_no_driver_available_list($request);
    }

    // get fake ride list
    public function get_fake_ride_list(GetFakeRideListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_fake_ride_list($request);
    }

    // get ride area list
    public function get_ride_area_list(GetRideAreaListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->get_ride_area_list($request);
    }

    // view area boundaries
    public function view_area_boundaries(ViewAreaBoundariesRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->view_area_boundaries($request);
    }

    // add area boundaries
    public function add_area_boundaries(AddAreaBoundariesRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->add_area_boundaries($request);
    }
   
    // delete ride 
    public function delete_ride(DeleteRideRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->delete_ride($request);
    }
}
