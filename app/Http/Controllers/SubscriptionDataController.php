<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\SubscriptionData;
use App\Http\Components\Component;
use App\Http\Datatables\SubscriptionDataDatatable;

class SubscriptionDataController extends Controller
{
    private $nav = 'subscriptionData';
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request) {

        $params = [
            'base_url' => route('SubscriptionDatas'),
            'dataload_url' => route('SubscriptionDatas_load'),
            'title' => "subscriptionData",
            'titles' => "subscriptionDatas",
            'parentTitles' => "",
            'icon' => "",
            'icons' => "",
            'create' => false,
            'filter' => true,
            'unsortable' => "",
            'columns' => [
                				
                [ "title" => "#", "width" => "5%", "filter" => ""],
                [ "title" => "AppId", "filter" => $this->filterText("AppId")],
                [ "title" => "subscriberId", "filter" => $this->filterText("subscriberId")],
                [ "title" => "otp", "filter" => $this->filterText("otp")],
                [ "title" => "count", "filter" => $this->filterText("count")],
                [ "title" => "device_id", "filter" => $this->filterText("device_id")],
            ],
        ];
        $params['message'] = $this->getAlert();
        $params['messageType'] = $this->getAlertCSSClass();

        return view('table', $params)->withNav($this->nav);
    }

    public function datatable(Request $request) {
        $datatable = new SubscriptionDataDatatable;
        return $datatable->datatable($request);
    }
}