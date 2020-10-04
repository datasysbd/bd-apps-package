<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Components\Curl;
use App\Http\Components\SmsSender;
use App\Http\Components\SubscriptionSender;
use App\USSDSub;
use App\SmsSaved;
use App\Content;
use App\AppPass;
use App\MessageData;
use App\SubscriptionData;

class SMSController extends Controller
{

    public function smsSend(Request $request)
    {
        $url = "https://developer.bdapps.com/sms/send";

        $message = $request->input('message');
        $app_id = $request->input('app_id');
        $password = $request->input('password');
        $sms_ob = new SmsSender($url, $app_id, $password);
        $response = $sms_ob->broadcast($message);
        // $ip = $request->ip();
        // $response['client_ip'] = isset($ip) ? $ip : 'Not Found';
        return $response;

    }


    /* public function cronSmsSend(Request $request)
     {

         $salt = "DF7AFB9CBA953DA385CA76882FEB3";

         if ($request->input('salt') == $salt) {
             $url = "https://developer.bdapps.com/sms/send";
             $app_id = "APP_014086";
             $obj = Content::orderBy('created_at', 'DESC')->where('is_sent', false)->get()->first();
             $message = isset($obj->content) ? $obj->content : "N/A";
             $password = "34a957801d34126bb54c592bab1a9dcf";
             $sms_ob = new SmsSender($url, $app_id, $password);

             if (!empty($obj)) {
                 $response = $sms_ob->broadcast($message);
                 $res_obj = json_decode($response);

                 if ($res_obj->statusCode == 'S1000') {
                     $obj->is_sent = true;
                     if ($obj->save()) {

                         $data['message'] = "SMS sent to all subscriber ! and db updated successfully ";
                         $data['response'] = $response;
                         return $data;
                     } else {
                         $data['message'] = "SMS sent to all subscriber ! but Database update error !! ";
                         $data['response'] = $response;
                         return $data;
                     }
                 } else {
                     $data['message'] = "SMS not sent check server response statusCode & statusDetails for more";
                     $data['response'] = $response;
                     return $data;
                 }

             } else {
                 $response['message'] = "Database is empty or no more unsent message available ! please insert content";
                 return $response;
             }
         }
         $data['alert'] = "ALERT !!!!!! OPERATION ABORTED ! ENCRYPTION KEY DOESN'T MATCH. THIS INCIDENT WILL BE RECORDED ALONG WITH IP_ADDR";
         return $data;
     }*/

//     public function smsSend(Request $request){

//         $app_id = $request->input('app_id');
//         $password = $request->input('password');
//
//         $dest_addr = $request->input('dest_addr');

// //        $message_json =  $this->getSendMessageJson($app_id, $password, $message, $dest_addr);

//         $arrayField = array(
//         "applicationId"=>"APP_014086",
//         "password"=>"34a957801d34126bb54c592bab1a9dcf",
//         "message"=>"Hello Friend",
//         "destinationAddresses"=>["tel:B%3C4syfNGoCtonwa/ENJ961lg1cmq6pWz0m+5mBnTliLT3aiDqPYAc9dpKD+QLV6GRnnHSc35zTH6h36G2aED48O0w=="]

//         );


//        $jsonObjectFields = json_encode($arrayField);


//         return $this->curlPOSTsms($jsonObjectFields);

//     }
//     //$app_id, $password, $message, $dest_addr
    public function getSendMessageJson()
    {
        return "{\n\t\"applicationId\": \"APP_014086\",\n\t\"password\": \"34a957801d34126bb54c592bab1a9dcf\",\n\t\"message\": \"hello there\",\n\t\"destinationAddresses\": [\"tel:AZ110uk76PIgB9RwcuA9JuF4N\\/SkIDEI2OIAKfBBRy8H6\\/W4Hi66VUqwA2zcEQe5VtB\\/YfQhPyp7XBVWmru2cwT1tow==\"]\n\t}";

    }

    public function curlPOSTsms($jsonObjectFields)
    {

        $url = "https://developer.bdapps.com/sms/send";

        $method = "POST";
        $header = [
            "Content-type: application/json",
            "Accept : application/json"
        ];
        $post_fields = $jsonObjectFields;

        return Curl::call($url, $method, $header, $post_fields);
    }

    /** This is for generating random 6 digit string for doctor's referral.
     *
     */
    public function generateRandomString($length, $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[rand(0, $max)];
        }
        return strtoupper($str);
    }

    public function sendSubsriptionSmsToSubscriber($app_id, $message, $subscriberId)
    {
        $url = "https://developer.bdapps.com/sms/send";
        $password = AppPass::where('AppId', $app_id)->pluck('password')->first();

        $sms_ob = new SmsSender($url, $app_id, $password);
        return $sms_ob->sms($message, $subscriberId);
    }

    public function getPlaystoreLink($app_id)
    {

        $link = AppPass::where('AppId', $app_id)->pluck('plink')->first();
        return $link;

    }


    public function smsReceive(Request $request)
    {

        // if request has requestId parameter that means user sending sms with some text.
        if (isset($request->requestId)) {
            $message = $request->input('message');
            $requestId = $request->input('requestId');
            $encoding = $request->input('encoding');
            $applicationId = $request->input('applicationId');
            $sourceAddress = $request->input('sourceAddress');
            $version = $request->input('version');

            $messageData = new MessageData();
            $messageData->message = $message;
            $messageData->requestId = $requestId;
            $messageData->encoding = $encoding;
            $messageData->applicationId = $applicationId;
            $messageData->sourceAddress = $sourceAddress;
            $messageData->version = $version;
            $otp = $this->generateRandomString(6);
            $messageData->otp = $otp;

            if ($messageData->save()) {
                $link = $this->getPlaystoreLink($applicationId);
                $link_msg = isset($link) ? 'Download this app from: ' . $link : "";
                $msg = "You have successfully subscribed to our service. Your code is:" . $otp . " Please use this Code to avail your service." . $link_msg . " Thank you ";
                $this->sendSubsriptionSmsToSubscriber($applicationId, $msg, $sourceAddress);
                $data['sucess'] = true;
                $data['message'] = "Data Saved";

            } else {
                $data['sucess'] = false;
                $data['message'] = "Messaeg Data saving error.";
            }


        } else {

            $version = $request->input('version');
            $applicationId = $request->input('applicationId');
            $subscriberId = $this->refineSubscriberId($request->input('subscriberId'));
            $status = $request->input('status');
            $otp = null;
            $frequency = $request->input('frequency');
            $timeStamp = $request->input('timeStamp');

            if ($status == "REGISTERED") {
                $subData = new SubscriptionData();
                $subData->appId = $applicationId;
                $subData->subscriberId = $this->refineSubscriberId($subscriberId);
                $otp = $this->generateRandomString(6);
                $subData->otp = $otp;

                if ($subData->save()) {

                    $link = $this->getPlaystoreLink($applicationId);
                    $link_msg = isset($link) ? 'Download this app from: ' . $link : "";
                    $msg = "You have successfully subscribed to our service. Your code is:" . $otp . " Please use this Code to avail your service." . $link_msg . " Thank you ";
                    $musk = $this->refineSubscriberId($subscriberId);
                    $data['msg'] = $msg;
                    $data['response'] = $this->sendSubsriptionSmsToSubscriber($applicationId, $msg, $musk);
                }

            }


            $sms = new SmsSaved();

            $sms->version = isset($version) ? $version : "";
            $sms->applicationId = isset($applicationId) ? $applicationId : "";
            $sms->subscriberId = isset($subscriberId) ? $this->refineSubscriberId($subscriberId) : "";
            $sms->status = isset($status) ? $status : "";
            $sms->otp_id = $otp;
            $sms->frequency = isset($frequency) ? $frequency : "";
            $sms->timeStamp = isset($timeStamp) ? $timeStamp : "";


            if ($sms->save()) {
                $data['sucess'] = true;
                $data['message'] = "Data Saved";
            }
        }
        return $data;


    }


    public function checkSubscriptionCodeOfSubscriber(Request $request)
    {

        $otp = $request->input('code');
        $device_id = $request->input('device_id');

        if (!empty($otp)) {
            $check = SubscriptionData::where('otp', $otp)->get()->first();
            if (!empty($device_id)) {

                $device_check = SubscriptionData::where('device_id', $device_id)->pluck('device_id')->first();

                if ($device_check !== $device_id) {
                    $data['message'] = "This OTP is already used in other device.";
                    return response()->json($data);
                }
            }
            if (empty($check)) {
                $data['is_there'] = false;
            } else {
                $check->device_id = isset($device_id) ? $device_id : null;
                $check->count = $check->count + 1;
                $check->save();
                $data['count'] = $check->count;
                $data['is_there'] = true;

            }

        } else {
            $data['message'] = "Code is not found in api parameter";
        }
        return response()->json($data);


    }


    public function addSubscriberPass(Request $request)
    {
        if ($request->pwd == "bdapps2019") {
            $app_id = isset($request->app_id) ? $request->app_id : null;
            $password = isset($request->password) ? $request->password : null;
            $plink = isset($request->plink) ? $request->plink : null;

            if ($app_id !== null && $password !== null) {
                $is_exist = AppPass::where("AppId", $app_id)->get()->first();
                if (empty($is_exist)) {
                    $app = new AppPass;
                    $app->AppId = $app_id;
                    $app->password = $password;
                    $app->plink = $plink;
                    $app->save();

                    $data = [
                        'app_id' => $app_id,
                        'password' => $password,
                        'plink' => $plink,
                    ];

                } else {
                    $apps = AppPass::where('AppId', $app_id)->pluck('id')->first();
                    $app = AppPass::find($apps);
                    $app->AppId = $app_id;
                    $app->password = $password;
                    $app->plink = $plink;
                    $app->save();

                    $data = [
                        'app_id' => $app_id,
                        'password' => $password,
                        'plink' => $plink,
                    ];

                }

            } else {
                return "you must specify app_id and password both";
            }
            return $data;
        } else {
            return "you are not authenticated";
        }
    }


    public function ussdReceive(Request $request)
    {
        $data['message'] = 'empty';
        if (isset($request)) {
            $ussd_msg = 'Thanks for the request. Please wait until you got a pop up asking to confirm your your subscription.';

            //$reponse_subscribe =
            // $data['ussd'] = response()->json($reponse_ussd);
            $reponse_subscribe = $this->resendOtp($this->refineSubscriberId($request->sourceAddress), $request->applicationId);
            $ussd_msg = $reponse_subscribe['message_ussd'];
            $data['ussd_msg'] = $ussd_msg;
            $reponse_ussd = $this->ussdSender($request->applicationId, $request->sessionId, $ussd_msg, $this->refineSubscriberId($request->sourceAddress));
            // $data['subscribe'] = $reponse_subscribe;

            $ussd = new USSDSub;

            $ussd->message = isset($request->message) ? $request->message : '';
            $ussd->requestId = isset($request->requestId) ? $request->requestId : '';
            $ussd->sessionId = isset($request->sessionId) ? $request->sessionId : '';
            $ussd->encoding = isset($request->encoding) ? $request->encoding : '';
            $ussd->AppId = isset($request->applicationId) ? $request->applicationId : '';
            $ussd->subscriberId = isset($request->sourceAddress) ? $this->refineSubscriberId($request->sourceAddress) : '';
            $ussd->version = isset($request->version) ? $request->version : '';

            if ($ussd->save()) {

                $subData = new SubscriptionData();
                $subData->appId = $request->applicationId;
                $subData->subscriberId = $this->refineSubscriberId($request->sourceAddress);
                $otp = $this->generateRandomString(6);
                $subData->otp = $otp;

                // $msg = "You have successfully subscribed to our service. Your code is:" . $otp ." Please use this Code to avail your service. Thank you ";
                // $this->sendSubsriptionSmsToSubscriber($request->applicationId, $msg, $request->sourceAddress);
                $data['sucess'] = true;
                $data['ussd_resp'] = isset($reponse_ussd) ? json_decode($reponse_ussd) : 'No response from';
                $data['subs_resp'] = isset($reponse_subscribe) ? $reponse_subscribe : 'No response from';
                $data['message'] = "Data Saved";
                $subData->save();
            } else {
                $data['sucess'] = false;
                $data['message'] = "Messaeg Data saving error.";
            }


        }


        //return response()->json($data);
    }


    // public function checkMessageDataOtp(Request $request){
    //     $otp = $request->input('code');
    //     $device_id = $request->input('device_id');

    //     if(!empty($otp)){
    //         $check = MessageData::where('otp' , $otp)->get()->first();
    //         if(!empty($device_id && $device_id !== null )){
    //             $device_check = MessageData::where('device_id' , $device_id)->pluck('device_id')->first();

    //             $allMessageOfThisDeviceId = MessageData::select('message')->where('device_id', $device_id)->get();
    //             foreach($allMessageOfThisDeviceId as $l){
    //                 array_push($arr, $l);
    //             }
    //                 $msgdata = isset($arr) ? $arr : "";
    //             if($device_check == null){

    //                 $check->device_id = isset($device_id) ? $device_id : null;
    //                 $check->save();

    //             }
    //             if($device_check !== $device_id){

    //                 $data['message'] = "This OTP is already used in other device.";
    //                 return response()->json($data);
    //             }
    //         }else{
    //             $data['message'] = "You have not sent device ID.";
    //                 return response()->json($data);
    //         }
    //         if(empty($check)){
    //             $data['is_there'] = false;
    //         }else{
    //             $check->device_id = isset($device_id) ? $device_id : null;
    //             $check->save();
    //             $data['is_there'] = true;
    //             $data['returned_message'] = isset($msgdata) ? $msgdata : "" ;

    //         }

    //     }else{
    //         $data['message'] = "Code is not found in api parameter";
    //     }
    //     return response()->json($data);


    // }


    public function checkMessageDataOtp(Request $request)
    {
        $otp = $request->input('code');
        $device_id = $request->input('device_id');

        if (isset($otp)) {
            if (isset($device_id)) {
                $check = MessageData::where('otp', $otp)->get()->first();

                if (isset($check)) {
                    $device_check = MessageData::where('otp', $otp)->pluck('device_id')->first();
                    if ($device_check == null || empty($device_check)) {
                        $check->device_id = $device_id;
                        $check->save();
                        $data['success'] = true;
                        $data['message'] = $check->message;
                    } else {
                        if ($device_id == $device_check) {
                            $arr = array();
                            $allMessageOfThisDeviceId = MessageData::select('message')->where('device_id', $device_id)->pluck('message');

                            foreach ($allMessageOfThisDeviceId as $list) {
                                array_push($arr, $list);
                            }
                            $msgdata = isset($arr) ? $arr : "";

                            $data['message'] = $msgdata;
                            $data['success'] = true;
                        } else {
                            $data['success'] = false;
                            $data['message'] = "Your device id not matched";
                        }
                    }
                } else {
                    $data['success'] = false;
                    $data['message'] = "OTP Not found";
                }
            } else {
                $data['message'] = "No Device ID";
            }
        } else {
            $data['message'] = "Code is not found in api parameter";
        }
        return response()->json($data);
    }


    public function ussdSender($app_id, $sessionId, $message, $destinationAddress, $ussdOperation = 'mt-fin')
    {
        $url = "https://developer.bdapps.com/ussd/send";
        $password = AppPass::where('AppId', $app_id)->pluck('password')->first();

        $arrayField = array("applicationId" => $app_id,
            "password" => $password,
            "message" => $message,
            "destinationAddress" => $destinationAddress,
            "sessionId" => $sessionId,
            "ussdOperation" => $ussdOperation,

        );

        $jsonObjectFields = json_encode($arrayField);

        $sms_ob = new SmsSender($url, $app_id, $password);

        return $sms_ob->sendRequest($jsonObjectFields, $url);
    }

    public function subscribe($app_id, $address)
    {

        $url = 'https://developer.bdapps.com/subscription/send';
        $password = AppPass::where('AppId', $app_id)->pluck('password')->first();
        $arrayField = array("applicationId" => $app_id,
            "password" => $password,
            "subscriberId" => $address,
            "version" => "1.0",
            "action" => "1");

        $jsonObjectFields = json_encode($arrayField);
        $sms_ob = new SmsSender($url, $app_id, $password);
        return $sms_ob->sendRequest($jsonObjectFields, $url);
    }

    public function unSubscribe($app_id, $address)
    {
        $url = 'https://developer.bdapps.com/subscription/send';
        $password = AppPass::where('AppId', $app_id)->pluck('password')->first();
        $arrayField = array("applicationId" => $app_id,
            "password" => $password,
            "subscriberId" => $address,
            "version" => "1.0",
            "action" => "0");
        $jsonObjectFields = json_encode($arrayField);
        $sms_ob = new SmsSender($url, $app_id, $password);
        return $sms_ob->sendRequest($jsonObjectFields, $url);
    }


    public function testApi(Request $request)
    {
        $ip = $request->ip();
        $message = $request->message;
        $data['client_ip'] = isset($ip) ? $ip : 'Not Found';
        $data['message'] = isset($message) ? $message : 'No Message';


        return response()->json($data);
    }

    //smnadim21
    public function mirror(Request $request)
    {
        $body = $request->type == 'GET' ? 'query' : 'json';

        $client = new Client();
        try {
            $res = $client->request(
                $request->type,
                $request->url,
                [$body => $request->data]
            );
        } catch (GuzzleException $e) {
            return $e;
        }

        return $res->getBody();
    }

    //editor:shakiba-e-nur
    public function checkStatus(Request $request)
    {
        if ($request->pwd == "bdapps2019") {
            $app_id = isset($request->app_id) ? $request->app_id : null;
            $password = isset($request->password) ? $request->password : null;
            $plink = isset($request->plink) ? $request->plink : null;

            if ($app_id !== null && $password !== null) {
                $is_exist = AppPass::where("AppId", $app_id)->get()->first();

                /*$apps = Sms::where('AppId' , $app_id)->pluck('id')->first();
                $app = Sms::find($apps);
                $app->AppId = $app_id;
                $app->password = $password;
                $app->plink = $plink;
                $app->save();*/
                $is_exist = SmsSaved::where("applicationId", $app_id)->get()->first();

                $data = [
                    'appId' => $is_exist['applicationId'],
                    'status' => $is_exist['status'],
                    'subscriberId' => $is_exist['subscriberId'],
                ];


            } else {
                return "you must specify app_id and password both";
            }
            return $data;
        } else {
            return "you are not authenticated";
        }
    }

    public function submitOtp(Request $request)
    {

        $otp = $request['code'];
        $device_id = $request['device_id'];

        if (!empty($otp)) {
            $check = SubscriptionData::where(['otp' => $otp, 'isActive' => true])->get()->first();
            if (empty($check)) {
                $data['isActive'] = false;
                $data['device_id'] = null;
            } else {
                $check->device_id = isset($device_id) ? $device_id : null;
                $check->count = $check->count + 1;
                $check->isActive = false;
                $check->save();
                $data['isActive'] = true;
                $data['device_id'] = $check->device_id;


            }
        } else {
            $data['message'] = "Code or Device Id not found in api parameter";
        }
        return response()->json($data);


    }

    public function checkSubscriptionStatus(Request $request)
    {
        $appid = $request->input('app_id');
        $device_id = $request->input('device_id');

        if (!empty($appid) || !empty($device_id)) {

            $subscription_data = SubscriptionData::where(['AppId' => $appid, 'device_id' => $device_id, 'isActive' => false])->get()->last();

            if (!empty($subscription_data)) {
                $otp_count = $subscription_data['count'];
                if ($otp_count == 0) {
                    $data['status'] = 205;
                    $data['data'] = null;
                    $data['message'] = "otp not activated";
                } else {
                    $subscriber_id = $subscription_data['subscriberId'];
                    $device_id_subscribed = $subscription_data['device_id'];
                    if ($device_id_subscribed == $device_id) {
                        $otp = $subscription_data['otp'];
                        $sms = SmsSaved::where(['applicationId' => $appid, 'subscriberId' => $subscriber_id, 'otp_id' => $otp])->get()->last();


                        if (!empty($sms)) {
                            $smsid = $sms['id'];
                            $sms2 = $this->getLastOtp($appid, $subscriber_id);


                            $currentSmsData = $sms;
                            if ($sms2 != null) {
                                $smsid2 = $sms2['id'];

                                if ($smsid2 > $smsid) {//last status subscribed
                                    $currentSmsData = $sms2;
                                }
                            }
                            $result['appId'] = $currentSmsData['applicationId'];
                            $result['status'] = $currentSmsData['status'];
                            $result['frequency'] = $currentSmsData['frequency'];
                            $result['version'] = $currentSmsData['version'];
                            $result['subscriberId'] = $currentSmsData['subscriberId'];
                            $result['otp'] = $otp;
                            $result['deviceId'] = $subscription_data['device_id'];
                            $data['status'] = 200;
                            $data['data'] = $result;
                            $data['message'] = "result ok";
                        } else {
                            $data['status'] = 203;
                            $data['data'] = null;
                            $data['message'] = "sms data not found";
                        }
                    } else {
                        $data['status'] = 206;
                        $data['data'] = null;
                        $data['message'] = "device id mismatch";
                    }

                }

            } else {
                $data['status'] = 202;
                $data['data'] = null;
                $data['message'] = "subscription data not found";
            }
        } else {
            $data['status'] = 201;
            $data['data'] = null;
            $data['message'] = "missing parameter:app_id or device_id";
        }
        return response()->json($data);
    }

    public function getLastOtp($appId, $subscriberId)
    {

        $sms_data = null;

        if (!empty($appid) || !empty($subscriberId)) {
            // $otp = Sms::where(['subscriberId'=>$subscriberId,'applicationId'=>$appid])->whereNotNull('otp_id')->get()->last();
            $sms_data = SmsSaved::where(['subscriberId' => $subscriberId, 'applicationId' => $appId, 'otp_id' => null])->get()->last();
        }
        return $sms_data;
    }

// smnadim21 smart  otp verification system.
    public function verifyOtp(Request $request)
    {
        $code = $request['code'];
        $device_id = $request['device_id'];
        $app_id = $request['app_id'];


        if (!empty($code) && !empty($device_id) && !empty($app_id)) {

            $subscription_data = SubscriptionData::where([
                'otp' => $code,
                'AppId' => $app_id,
            ])->get()->last();

            //$data['data'] = $subscription_data;


            if ($subscription_data != null) {

                $subscription_status = $this->getSubscriptionStatus($subscription_data['subscriberId'], $app_id);

                /*SmsSaved::where([
                    'applicationId' => $app_id,
                    'subscriberId' => $subscription_data['subscriberId']
                ])->get()->last();*/

                //$data['subscription'] = $subscription_status;

                if ($subscription_data['device_id'] == null || empty($subscription_data['device_id'])) {
                    if ($subscription_status['status'] === 'UNREGISTERED') {
                        $data['message'] = "user already unregistered this APP";
                        $data["valid_subscriber"] = false;
                        $data["existing_subscriber"] = true;
                    } else if ($subscription_status['status'] === 'REGISTERED') {
                        $data['message'] = "Subscription Successful";
                        $data["valid_subscriber"] = true;
                        $data["existing_subscriber"] = false;
                    }

                    $subscription_data['device_id'] = $device_id;
                    $subscription_data['count'] += 1;
                    $subscription_data->save();


                } else if ($subscription_data['device_id'] == $device_id) {
                    $data['message'] = "existing user";
                    $data["existing_subscriber"] = true;
                    if ($subscription_status != null) {
                        $data['message'] = "existing user >> " . $subscription_status['status'];

                        if ($subscription_status['status'] === 'UNREGISTERED') {
                            $data["valid_subscriber"] = false;
                        } else if ($subscription_status['status'] === 'REGISTERED') {
                            $data["valid_subscriber"] = true;
                        }
                    } else {
                        $data['message'] = "existing user >> Possible Case: Custom Entry";
                        $data["valid_subscriber"] = false;
                        $data["existing_subscriber"] = false;

                    }
                } else {
                    $data['message'] = "otp and device miss match || Possible reason: OTP used in other device";
                    $data["valid_subscriber"] = false;
                    $data["existing_subscriber"] = false;
                }
            } else {
                $data["valid_subscriber"] = false;
                $data["existing_subscriber"] = false;
                $data['message'] = "subscription data not found! || invalid otp for this app  ";
            }

        } else if (empty($code) && !empty($device_id) && !empty($app_id)) {
            $data['message'] = "code missing! >> app_id okay >> device_id okay  ";

            $subscription_data = SubscriptionData::where([
                'device_id' => $device_id,
                'AppId' => $app_id
            ])->get()->last();

            if ($subscription_data != null) {
                $data["existing_subscriber"] = true;
                $data['message'] = "existing user >> device_id";

                // $data['data'] = $subscription_data;

                $subscription_status = $this->getSubscriptionStatus($subscription_data['subscriberId'], $app_id);

                /*SmsSaved::where([
                    'applicationId' => $app_id,
                    'subscriberId' => $subscription_data['subscriberId']
                ])->get()->last();*/

                //$data['subscription'] = $subscription_status;

                if ($subscription_status != null) {
                    $data['message'] = "existing user >> " . $subscription_status['status'];
                    if ($subscription_status['status'] === 'UNREGISTERED') {
                        $data["valid_subscriber"] = false;
                    } else if ($subscription_status['status'] === 'REGISTERED') {
                        $data["valid_subscriber"] = true;
                    }
                }

            } else {
                $data["valid_subscriber"] = false;
                $data["existing_subscriber"] = false;
                $data['message'] = "device not recognized! ";
            }

        } else {
            $data['message'] = "params missing!  ";
        }

        return response()->json($data);

    }


    public function resendOtp($subscriber_id, $app_id)
    {
        $data['message'] = "empty response!";
        if (!empty($subscriber_id) && !empty($app_id)) {
            $subscription_data = $this->getSubscriptionStatus($subscriber_id, $app_id);
            if ($subscription_data != null) {
                // $data['data'] = $subscription_data;
                if ($subscription_data['status'] === 'UNREGISTERED') {
                    $data['subscribe'] = json_decode($this->subscribe($app_id, $subscriber_id));
                    $data["valid_subscriber"] = false;
                    $data['message'] = "trying to subscribe!";
                    $data['message_ussd'] = 'Thanks for the request. Please wait until you got a pop up asking to confirm your your subscription.';
                } else if ($subscription_data['status'] === 'REGISTERED') {
                    $data["valid_subscriber"] = true;
                    $data['message'] = "resending otp!";
                    $data['message_ussd'] = 'you are already subscribed! resending your OTP';
                    $data["otp"] = SubscriptionData::where(['subscriberId' => $subscriber_id, 'AppId' => $app_id,])->get('otp')->last()['otp'];
                    $link = $this->getPlaystoreLink($app_id);
                    $link_msg = isset($link) ? 'Download this app from: ' . $link : "";
                    $msg = "You have successfully subscribed to our service. Your code is:" . $data['otp'] . " Please use this Code  or open http://activate?otp=" . $data['otp'] . " with your APP to avail your service." . $link_msg . " Thank you ";
                    $musk = $this->refineSubscriberId($subscriber_id);
                    $data['resend_otp'] = json_decode($this->sendSubsriptionSmsToSubscriber($app_id, $msg, $musk));
                    $data['msg'] = $msg;

                }
            } else {
                $data['message'] = "no data! >> new!";
                $data['message_ussd'] = 'Thanks for the request. Please wait until you got a pop up asking to confirm your your subscription.';
                $data['subscribe'] = $this->subscribe($app_id, $subscriber_id);
            }

        } else {
            $data['message'] = "params missing!";
        }


        return $data;
    }

    public function getSubscriptionStatus($subscriber_id, $app_id)
    {
        return SmsSaved::where('applicationId', $app_id)
            ->where('subscriberId', 'like', '%' . $this->removeSubscriberIdHead($subscriber_id) . "%")
            ->get()->last();
    }

    public function refineSubscriberId($subscriber_id)
    {
        return $this->has_prefix($subscriber_id, "tel:") ? $subscriber_id : "tel:" . $subscriber_id;
    }


    public function removeSubscriberIdHead($subscriber_id)
    {
        $head = "tel:";
        return $this->has_prefix($subscriber_id, $head)
            ? substr($subscriber_id, strlen($head), strlen($subscriber_id))
            : $subscriber_id;
    }

    function has_prefix($string, $prefix)
    {
        return substr($string, 0, strlen($prefix)) == $prefix;
    }


    //api for testing perpouse
    /*  function testf(Request $request)
      {
          return $this->removeSubscriberIdHead($request->str);
      }*/

}
