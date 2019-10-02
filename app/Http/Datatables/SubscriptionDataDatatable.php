<?php

namespace App\Http\Datatables;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\SubscriptionData;
use App\Http\Components\Component;
use App\Http\Datatables\Datatable;

class SubscriptionDataDatatable extends Datatable
{
    private $nav = 'subscriptionData';

    public function datatable(Request $request) {

        $results = new SubscriptionData;

        $AppId = isset($request->AppId) ? $request->AppId : null;
        $subscriberId = isset($request->subscriberId) ? $request->subscriberId : null;
        $otp = isset($request->otp) ? $request->otp : null;
        $count = isset($request->count) ? $request->count : null;
        $device_id = isset($request->device_id) ? $request->device_id : null;

        $updated_from = isset($request->updated_from) ? $request->updated_from : null;
        $updated_to = isset($request->updated_to) ? $request->updated_to : null;

        if ($AppId) {
            $results = $results->where('AppId', 'like', '%'.$AppId.'%');
        }
        if ($subscriberId) {
            $results = $results->where('subscriberId', 'like', '%'.$subscriberId.'%');
        }
        if ($otp) {
            $results = $results->where('otp', 'like', '%'.$otp.'%');
        }
        if ($count) {
            $results = $results->where('count', 'like', '%'.$count.'%');
        }
        if ($device_id) {
            $results = $results->where('device_id', 'like', '%'.$device_id.'%');
        }
        $tableColumns = [
            "",
            "AppId",
            "subscriberId",
            "otp",
            "count",
            "device_id",
            ""
        ];
        $sortColumn = $request->order[0]['column'];
        $sortDir = $request->order[0]['dir'];
        $sort_field = $tableColumns[$sortColumn];

        switch ($sort_field) {
            case "AppId":
                $results = $results->orderBy('AppId', $sortDir);
                break;
            case "subscriberId":
                $results = $results->orderBy('subscriberId', $sortDir);
                break;
            case "otp":
                $results = $results->orderBy('otp', $sortDir);
                break;
            case "count":
                $results = $results->orderBy('count', $sortDir);
                break;
            case "device_id":
                $results = $results->orderBy('device_id', $sortDir);
                break;
            case "updated_at":
                $results = $results->orderBy('updated_at', $sortDir);
                break;
            default:
                $results = $results->orderBy('updated_at', 'desc');
                break;
        }

        $results = $results->get();

        $iTotalRecords = $results->count();
        $iDisplayLength = intval($request->length);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->start);
        $sEcho = intval($request->draw);

        $data = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        for ($i = $iDisplayStart; $i < $end; $i++) {
            $result = $results[$i];
            $data[] = [
                $i+1,
                isset($result->AppId) ? $result->AppId : "",
                isset($result->subscriberId) ? $result->subscriberId : "",
                isset($result->otp) ? $result->otp : "",
                isset($result->count) ? $result->count : "",
                isset($result->device_id) ? $result->device_id : "",
            ];
        }
        $this->data = $data;
        $this->draw = $sEcho;
        $this->total = $iTotalRecords;
        $this->filtered = $iTotalRecords;
        $this->status = true;
        return $this->outputDatatable();
    }
}