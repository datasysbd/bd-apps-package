<?php

namespace App\Http\Components;

class BDAppsApi {
    private $url;
    public $app_id = "";
    public $password = "";
    public $subscriberId = "";
    private $status = false;
    private $message = "Invalid Request";
    private $statusCode = "E1312"; // BDAPPS ERROR CODE // FOR SUCCESS CODE: "S1000"
    private $statusDetail = "Request Invalid.";

    public function isInvalid() {
        $invalid = false;
        $message = "";
        if(!isset($this->app_id)) {
            $message .= " AppId not found!";
            $invalid = true;
        }
        if(!isset($this->password)) {
            $message .= " Password not found!";
            $invalid = true;
        }
        if(!isset($this->subscriberId)) {
            $message .= " SubscriberId not found!";
            $invalid = true;
        }
        $this->message = $message;
        return $invalid;
    }

    public function errorOutput() {
        return json_encode([
            'statusCode' => $this->statusCode,
            'statusDetail' => $this->statusDetail,
        ]);
    }

    public function subscribe() {
        if($this->isInvalid()) {
            return $this->errorOutput();
        }
        $json = json_encode([
            "applicationId" => $this->app_id,
            "password" => $this->password,
            "subscriberId" => "tel:".$this->subscriberId,
            "version" => "1.0",
            "action" => "1"
        ]);

        $this->url = 'https://developer.bdapps.com/subscription/send';
        return $this->sendRequest($json);
    }

    public function unSubscribe(){
        if($this->isInvalid()) {
            return $this->errorOutput();
        }
        $json = json_encode([
            "applicationId" => $this->app_id,
            "password" => $this->password,
            "subscriberId" => "tel:".$this->subscriberId,
            "version" => "1.0",
            "action" => "0"
        ]);

        $this->url = 'https://developer.bdapps.com/subscription/send';
        return $this->sendRequest($json);
    }

    public function getstatus(){
        if($this->isInvalid()) {
            return $this->errorOutput();
        }
        $json = json_encode([
            "applicationId" => $this->app_id,
            "password" => $this->password,
            "subscriberId" => "tel:".$this->subscriberId,
        ]);

        $this->url = 'https://developer.bdapps.com/subscription/send';
        return $this->sendRequest($json);
    }
    
    public function sendRequest($jsonStream){

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;

    }

}