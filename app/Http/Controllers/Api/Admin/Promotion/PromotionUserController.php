<?php

namespace App\Http\Controllers\Api\Admin\Promotion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Promotion\RedeemPromotionRequest;
use App\Http\Requests\Api\Admin\Promotion\GetUserPromotionListRequest;

class PromotionUserController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    // get user promotion list
    public function get_user_promotion_list(GetUserPromotionListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_user_promotion_list($request);

    }

    //  redeem promotion 
    public function redeem_promotion(RedeemPromotionRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->redeem_promotion($request);

    }

}
