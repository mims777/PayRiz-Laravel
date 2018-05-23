<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller {
    public function NewPay(Request $request){
        $pay = new PaymentPayRizController();
        $pay->CallBackUrl = '/payment/verify'; // Your Call Back Address
        $pay->OptionalData = $request['payInfo'];
        $pay->OrderId = $request['orderid'];
        $pay->Amount = $request['amount'];
        $pay->PayRequest ();
    }
    public function Verify(){
        $verify = new PaymentPayRizController();
        $check = $verify->Verify ();
       if ($check == true && $check != 0){
            // Payment Successfully
        }
        else{
            // Payment Failed
        }
    }
}
