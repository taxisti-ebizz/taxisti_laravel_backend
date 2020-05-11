<?php

namespace App\Http\Controllers\Api\Admin\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;

class DashboardController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    //  get dashboard data
    public function get_dashboard_data()
    {
        return $this->panel->get_dashboard_data();
    }
}
