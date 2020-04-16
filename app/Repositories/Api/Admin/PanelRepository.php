<?php

namespace App\Repositories\Api\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PanelRepository extends Controller
{
    // get promotion list
    public function get_promotion_list($request)
    {
        $promotion_list = DB::table('taxi_promotion')
            ->orderByRaw('id DESC')
            ->paginate(10)->toArray();

        if($promotion_list['data'])
        {
            $list = [];
            foreach ($promotion_list['data'] as $promotion) {
                
                $promotion->promo_image = $promotion->promo_image != "" ?  env('AWS_S3_URL') . $promotion->promo_image : '';
                $list[] = $promotion;
            }
            
            $promotion_list['data'] = $list;

            return response()->json([
                'status'    => true,
                'message'   => 'Promotion list', 
                'data'    => $promotion_list,
            ], 200);

        }
        else
        {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }        
    }

    // update promotion detail
    public function update_promotion_detail($request)
    {

        $input = $request->except(['id']);
        $input['updated_at'] = date('Y-m-d H:m:s');

        // promo_image handling 
        if ($request->file('promo_image')) {

            // delete promo_image
            $promotion = DB::table('taxi_promotion')->where('id',$request['id'])->first();
            Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

            $promo_image = $request->file('promo_image');
            $imageName = 'uploads/promo_image/' . time() . '.' . $promo_image->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($promo_image), 'public');
            $input['promo_image'] = $imageName;
        }

      
        $promotion = DB::table('taxi_promotion')
            ->where('id',$request['id'])
            ->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion updated', 
            'data'    => array(),
        ], 200);

    }

    // delete promotion
    public function delete_promotion($request, $id)
    {
        // delete promo_image
        $promotion = DB::table('taxi_promotion')->where('id',$id)->first();
        Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

        DB::table('taxi_promotion')->where('id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion deleted', 
            'data'    => array(),
        ], 200);

    }
}