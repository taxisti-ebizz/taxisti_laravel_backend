<?php

namespace App\Http\Controllers\Api\Admin\RideArea;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\RideRepository;
use App\Http\Requests\Api\Admin\Ride\AddAreaBoundariesRequest;

class AddRideAreaController extends Controller
{
    protected $ride;

    public function __construct()
    {
        $this->ride = new RideRepository;
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

}
