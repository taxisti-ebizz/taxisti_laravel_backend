<?php

namespace App\Http\Controllers\Api\Admin\Review;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\DriverRepository;
use App\Http\Requests\Api\Admin\Driver\GetDriverReviewstRequest;
use App\Http\Requests\Api\Admin\Driver\ViewDriverReviewstRequest;

class DriverReviewController extends Controller
{
    protected $driver;

    public function __construct()
    {
        $this->driver = new DriverRepository;
    }
    
    // get driver reviews
    protected function get_driver_reviews(GetDriverReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->get_driver_reviews($request);
    }

    // view driver reviews
    protected function view_driver_reviews(ViewDriverReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->driver->view_driver_reviews($request);
    }

}
