<?php

namespace App\Http\Datatables;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Message;
use App\Http\Components\Component;
use App\Http\Datatables\Datatable;

class MessageDatatable extends Datatable
{
    private $nav = 'messages';

    public function datatable(Request $request) {

        $results = new Message;

        $version = isset($request->version) ? $request->version : null;
        $applicationId = isset($request->applicationId) ? $request->applicationId : null;
        $subscriberId = isset($request->subscriberId) ? $request->subscriberId : null;
        $status = isset($request->status) ? $request->status : null;
        $frequency = isset($request->frequency) ? $request->frequency : null;
        $timeStamp = isset($request->timeStamp) ? $request->timeStamp : null;

        $updated_from = isset($request->updated_from) ? $request->updated_from : null;
        $updated_to = isset($request->updated_to) ? $request->updated_to : null;

        if ($version) {
            $results = $results->where('version', 'like', '%'.$version.'%');
        }
        if ($applicationId) {
            $results = $results->where('applicationId', 'like', '%'.$applicationId.'%');
        }
        if ($subscriberId) {
            $results = $results->where('subscriberId', 'like', '%'.$subscriberId.'%');
        }
        if ($status) {
            $results = $results->where('status', 'like', '%'.$status.'%');
        }
        if ($frequency) {
            $results = $results->where('frequency', 'like', '%'.$frequency.'%');
        }
        if ($timeStamp) {
            $results = $results->where('timeStamp', 'like', '%'.$timeStamp.'%');
        }
        $tableColumns = [
            "",
            "version",
            "applicationId",
            "subscriberId",
            "status",
            "frequency",
            "timeStamp",
            ""
        ];
        $sortColumn = $request->order[0]['column'];
        $sortDir = $request->order[0]['dir'];
        $sort_field = $tableColumns[$sortColumn];

        switch ($sort_field) {
            case "version":
                $results = $results->orderBy('version', $sortDir);
                break;
            case "applicationId":
                $results = $results->orderBy('applicationId', $sortDir);
                break;
            case "subscriberId":
                $results = $results->orderBy('subscriberId', $sortDir);
                break;
            case "status":
                $results = $results->orderBy('status', $sortDir);
                break;
            case "frequency":
                $results = $results->orderBy('frequency', $sortDir);
                break;
            case "timeStamp":
                $results = $results->orderBy('timeStamp', $sortDir);
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
                isset($result->version) ? $result->version : "",
                isset($result->applicationId) ? $result->applicationId : "",
                isset($result->subscriberId) ? $result->subscriberId : "",
                isset($result->status) ? $result->status : "",
                isset($result->frequency) ? $result->frequency : "",
                isset($result->timeStamp) ? $result->timeStamp : "",
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