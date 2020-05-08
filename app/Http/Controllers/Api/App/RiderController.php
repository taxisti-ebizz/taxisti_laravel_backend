<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\RiderRepository;
use App\Http\Requests\Api\App\Rider\GetDriverRequest;

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

}
