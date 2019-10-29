<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\BDAppsApi;
use App\AppPass;

class SubscriberController extends Controller
{

    public function subscribe(Request $request) {
        $subscriberId = $request->header("msisdn");

        if(!$subscriberId) {
            // return json_encode([
            //     'statusCode' => "E1312",
            //     'statusDetail' => "Please connect with Robi or Airtel number.",
            // ]);    
        }else {
            $appId = $request->app_id;
            $app_pass = AppPass::where('AppId', $appId)->first();
            if($app_pass) {
                
                $api = new BDAppsApi;
                $api->app_id = $appId;
                $api->password = $app_pass->password;
                $api->subscriberId = $subscriberId;
                $api->subscribe();
            }
        }

        if(isset($request->file_path)) {
            return redirect($request->file_path);
        }else {
            echo "File missing!";
        }
    }
}
