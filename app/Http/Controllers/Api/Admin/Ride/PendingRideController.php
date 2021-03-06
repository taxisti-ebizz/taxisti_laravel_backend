<?php

namespace App\Http\Controllers\Api\Admin\Ride;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\GetPendingRideListRequest;

class PendingRideController extends Controller
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


}
