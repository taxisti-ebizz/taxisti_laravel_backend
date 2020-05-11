<?php

namespace App\Http\Controllers\Api\Admin\Page;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Page\EditPageRequest;
use App\Http\Requests\Api\Admin\Page\DeletePageRequest;
use App\Http\Requests\Api\Admin\Page\GetPageListRequest;

class PageController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    //  get page list 
    public function get_page_list(GetPageListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_page_list($request);
    }

    //  edit page  
    public function edit_page(EditPageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->edit_page($request);
    }

    // delete page 
    public function delete_page(DeletePageRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_page($request, $id);

    }
}
