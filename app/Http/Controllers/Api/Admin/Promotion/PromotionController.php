<?php

namespace App\Http\Controllers\Api\Admin\Promotion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Promotion\DeletePromotionRequest;
use App\Http\Requests\Api\Admin\Promotion\GetPromotionListRequest;
use App\Http\Requests\Api\Admin\Promotion\UpdatePromotionDetailRequest;

class PromotionController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    // get promotion list
    public function get_promotion_list(GetPromotionListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_promotion_list($request);

    }

    // update promotion detail
    public function update_promotion_detail(UpdatePromotionDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->update_promotion_detail($request);

    }

    // delete promotion 
    public function delete_promotion(DeletePromotionRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_promotion($request, $id);

    }


}
