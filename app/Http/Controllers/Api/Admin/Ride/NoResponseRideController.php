<?php

namespace App\Http\Controllers\Api\Admin\Ride;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\GetNoResponseRideListRequest;

class NoResponseRideController extends Controller
{
    protected $ride;

    public function __construct()
    {
        $this->ride = new RideRepository;
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
