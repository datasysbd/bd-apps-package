<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Components\Curl;

class PublicController extends Controller
{

    public function ip_get(Request $request)
    {
        return request()->server();
    }

    public function ip_post(Request $request)
    {
        echo isset($_SERVER['HTTP_MSISDN']) ? $_SERVER['HTTP_MSISDN'] : "nothing!";
        $msisdn = $request->header('msisdn');
        return isset ($msisdn) ? $msisdn : "no!";
    }

    public function ip_test()
    {
        $val_get = Curl::call(route("ip_get"), "GET");
        $val_post = Curl::call(route("ip_post"), "POST");
        print_r($val_get);
        print_r($val_post);
    }
}
