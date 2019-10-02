<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Message;
use App\Http\Components\Component;
use App\Http\Datatables\MessageDatatable;

class MessageController extends Controller
{
    private $nav = 'messages';
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request) {

        $params = [
            'base_url' => route('messages'),
            'dataload_url' => route('messages_load'),
            'title' => "messages",
            'titles' => "messages",
            'parentTitles' => "",
            'icon' => "",
            'icons' => "",
            'create' => false,
            'filter' => true,
            'unsortable' => "",
            'columns' => [
                [ "title" => "#", "width" => "5%", "filter" => ""],
                [ "title" => "version", "filter" => $this->filterText("version")],
                [ "title" => "applicationId", "filter" => $this->filterText("applicationId")],
                [ "title" => "subscriberId", "filter" => $this->filterText("subscriberId")],
                [ "title" => "status", "filter" => $this->filterText("status")],
                [ "title" => "frequency", "filter" => $this->filterText("frequency")],
                [ "title" => "timeStamp", "filter" => $this->filterText("timeStamp")],
            ],
        ];
        $params['message'] = $this->getAlert();
        $params['messageType'] = $this->getAlertCSSClass();

        return view('table', $params)->withNav($this->nav);
    }

    public function datatable(Request $request) {

        $datatable = new MessageDatatable;
        return $datatable->datatable($request);
    }
}