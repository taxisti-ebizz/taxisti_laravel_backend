<?php

namespace App\Http\Controllers\Api\Admin\RideArea;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\GetRideAreaListRequest;
use App\Http\Requests\Api\Admin\Ride\ViewAreaBoundariesRequest;
use App\Http\Requests\Api\Admin\Ride\DeleteAreaBoundariesRequest;

class RideAreaController extends Controller
{
    protected $ride;

    public function __construct()
    {
        $this->ride = new RideRepository;
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

    // delete area boundaries
    public function delete_area_boundaries(DeleteAreaBoundariesRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->ride->delete_area_boundaries($request, $id);
    }

}
