<?php

namespace App\Http\Controllers\Api\Admin\DriverOnlineLog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\DriverRepository;
use App\Http\Requests\Api\Admin\Driver\GetDriverOnlineLogRequest;

class DriverOnlineLogController extends Controller
{
    protected $driver;

    public function __construct()
    {
        $this->driver = new DriverRepository;
    }

    // get driver online log
    protected function get_driver_online_log(GetDriverOnlineLogRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->get_driver_online_log($request);
    }

}
