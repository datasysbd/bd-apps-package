<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\AppPass;
use App\caas;
use App\SubscriptionData;
use Illuminate\Http\Request;

class CaasController extends Controller
{
    //

    public function requestCAAS(Request $request)
    {
        $amount = $request['amount'];
        $device_id = $request['device_id'];
        $app_id = $request['app_id'];
        $item_code = $request['item_code'];
        $data['message'] = 'empty response';
        $data['payment_status'] = false;

        if (!empty($amount) && !empty($device_id) && !empty($app_id) && !empty($item_code)) {

            $subscription_data = SubscriptionData::where(['device_id' => $device_id, 'AppId' => $app_id])->get()->last();
            if ($subscription_data != null) {
                $data['message'] = 'subscriber data found !';

                //
                // $total = caas::all()->count();
                // $data['externalTrxId'] = $app_id . '-' . str_pad($total + 1, 7, "0", STR_PAD_LEFT);

                $purchase_info = caas::where([
                    'device_id' => $device_id,
                    'applicationId' => $app_id,
                    'externalTrxId' => $item_code
                ])->get()->last();

                if ($purchase_info != null) {
                    // $data['$purchase_info'] = $purchase_info;
                    if ($purchase_info['statusCode'] == "S1000") {
                        $data['message'] = 'item purchased already';
                        $data['payment_status'] = true;
                    } else {
                        $data['message'] = 're-trying to purchase';
                        $data['payment_status'] = false;
                        return response()->json($this->newPurchaseRequest($purchase_info, $data));
                    }

                } else {
                    $data['message'] = 'new item';

                    $caas = new caas();
                    $caas->amount = $amount;
                    $caas->applicationId = $app_id;
                    $caas->device_id = $device_id;
                    $caas->subscriberId = $subscription_data['subscriberId'];
                    $caas->externalTrxId = $item_code;

                    return response()->json($this->newPurchaseRequest($caas, $data));


                }
            } else {
                $data['message'] = 'subscriber data not found, please subscribe first';
                $data['status'] = false;
            }

            return response()->json($data);
        } else {
            $data['message'] = 'params missing!';
            return response()->json($data);
        }

    }

    public function newPurchaseRequest($caas, $data)
    {

        $body = "json";
        $type = 'POST';
        $url = 'https://developer.bdapps.com/caas/direct/debit';

        $body_json['externalTrxId'] = $data['item_code'] = $caas->externalTrxId;;
        $body_json['amount'] = $data['amount'] = $caas->amount;
        $body_json['applicationId'] = $data['applicationId'] = $caas->applicationId;
        $body_json['password'] = AppPass::where('AppId', $caas->applicationId)->pluck('password')->first();
        $body_json['subscriberId'] = $caas->subscriberId;
        $body_json['paymentInstrumentName'] = "Mobile Account";

        //=========================HTTP RequestClient=====
        $client = new Client();

        try {
            $res = $client->request(
                $type,
                $url,
                [$body => $body_json]
            );

            $data['message'] = 'trying....';
            $response = $res->getBody();
            $data['http_code'] = $res->getStatusCode();
            $json_response = json_decode($response->getContents(), true);

            var_dump($response->getContents());

            $data['data'] = $json_response;
            if ($json_response != null) {
                $data['message'] = $res->getStatusCode() . " " . $res->getReasonPhrase() . " Request Successful";
                $caas->raw_response = json_encode($json_response);
                $caas->statusCode = $json_response['statusCode'];
                $caas->statusDetail = $json_response['statusDetail'];
                if ($json_response['statusCode'] == "S1000") {
                    $caas->internalTrxId = $json_response['internalTrxId'];
                    $data['message'] = "Payment Successful";
                    $data['payment_status'] = true;

                } else {
                    $data['message'] = "Payment Failed!";
                    $data['payment_status'] = false;
                }

            } else {
                $data['data'] = $response->getContents();
            }
            $data['status'] = true;

        } catch
        (GuzzleException $e) {
            $data['message'] = 'maybe failed!';
            $data['status'] = false;
            $caas->raw_response = $e->getMessage();
            $data['data'] = $e;

        }

        if ($caas->save()) {
            $data['status'] = true;
        } else {
            $data['status'] = false;
            $data['message'] = 'data saving failed!';
        }

        return $data;

    }
}
