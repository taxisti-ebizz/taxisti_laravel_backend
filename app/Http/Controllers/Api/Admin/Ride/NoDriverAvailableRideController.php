<?php

namespace App\Http\Controllers\Api\Admin\Ride;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\GetNoDriverAvailableListRequest;

class NoDriverAvailableRideController extends Controller
{
    protected $ride;

    public function __construct()
    {
        $this->ride = new RideRepository;
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
}
