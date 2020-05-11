<?php

namespace App\Http\Controllers\Api\Admin\Review;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\UserRepository;
use App\Http\Requests\Api\Admin\User\GetRiderReviewstRequest;
use App\Http\Requests\Api\Admin\User\ViewRiderReviewstRequest;

class UserReviewController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new UserRepository;
    }

    // get rider reviews
    protected function get_rider_reviews(GetRiderReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->get_rider_reviews($request);
    }

    // view rider reviews
    protected function view_rider_reviews(ViewRiderReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->view_rider_reviews($request);
    }
}
