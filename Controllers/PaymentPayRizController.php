<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentPayRizController extends Controller
{
    protected $PayRequestUrl = 'https://my.payriz.ir/?a=payment.payRequest';
    protected $merchant_id = 'xxxxxxxx'; // کدپذیرنده
    protected $terminal_key = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx1'; // کلید تراکنش
    public $Amount = 0;
    public $OrderId = 0;
    public $OptionalData = [];
    public $CallBackUrl = '';
    // public
    /**
     * @param $str
     * @param $key
     * @return string
     */
    private function encrypt( $str, $key)
    {
        // $key = base64_decode($key);
        // $block = mcrypt_get_block_size("tripledes", "ecb");
        // $pad = $block - (strlen($str) % $block);
        // $str .= str_repeat(chr($pad), $pad);
        // $ciphertext = mcrypt_encrypt("tripledes", $key, $str,"ecb");
        // return base64_encode($ciphertext);
        $d = file_get_contents ('http://kouroshsaeeidi.com/index.php?key='.$key.'&str='.$str);
        return $d;
    }
    private function PayRizError($ErrorCode){
        $response = null;
        switch ($ErrorCode){
            case 1:$response=['Message' => 'کد پزیرنده صحیح نیست به سامانه پی ریز مراجعه کرده و با مراجعه به صفحه درگاه پرداخت مقدار کد پزیرنده را دریافت کنید', 'Fix' => 'https://my.payriz.ir/'];break;

            case 2:$response=['Message' => "رمزنگاری فایل ها با خطا مواجعه شده است" , "Fix" => 'PaymentPayRizController -> function encrypt '];break;

            case 3:$response=['Message' => 'آی پی پزیرنده معتبر نیست' , 'Fix' => 'https://my.payriz.ir/'];break;

            case 4:$response=['Message' => 'دامنه پزیرنده معتبر نیست' , 'Fix' => 'https://my.payriz.ir/'];break;

            case 5:$response=['Message' => 'خطای داخلی. دوباره تلاش کنید و یا پشتیبانی سایت تماس حاصل فرمایید' , 'Fix' => 'null'];break;

            case 6:$response=['Message' => 'مقدار ارسالی کامل نیست' , 'Fix' => 'OrderId Not Null'];break;

            case 7:$response=['Message' => 'درخواست تکراری میباشد.' , 'Fix' => 'null'];break;

            case 8:$response=['Message' => 'پارمتر مسیر بازگشت از درگاه پرداخت نباید خالی باشد' , 'Fix' => 'Callback Not Null'];break;

            case 9:$response =['Message' => 'درخواست ارسالی با مقدار مد سفارش قابل قبول نیست' , 'Fix' => 'OrderId Type Int'];break;

            case 10:$response=['Message' => 'این سفارش قبلا پرداخت شده است با یک مقدار کد سفارش جدید تلاش کنید' , 'Fix' => "OrderID Payment Successfully Try Again New OrderId"];break;

            case 11:$response=['Message' => 'مقدار تراکنش کمتر از سقف تایین شده پی ریز میباشد' , 'Fix' => "Amount False"];break;

            case 12:$response=['Message' => 'مقدار توکن ارسال شده معتبر نیست' , 'Fix' => 'Check Your Token'];break;

            case 13:$response=['Message' => 'مقدار توکن ارسالی تکراری میباشد و یا قبلا استفاده شده است' , 'Fix' => 'Check Your Token'];break;

            case 14:$response=['Message' => 'مقدار توکن ارسالی تکراری میباشد و یا قبلا استفاده شده است' , 'Fix' => 'Check Your Token'];break;

            case 15:$response=['Message' => 'مقدار توکن ارسالی قبلا تایید شده است' , 'Fix' => 'Your Token Is Verify last'];break;

            case 16:$response=['Message' => 'مقدار کد سفارش توسط بانک تایید نمیشود لطفا یک مقدار جدید تلاش کنید' , 'Fix' => 'Change Your OrderId Value'];break;

            case 17:$response=['Message' => 'بانک پاسخگو نیست بعدا تلاش کنید' , 'Fix' => 'TryAgain'];break;
        }
        return $response;
    }
    private function CallAPI($url, $data = false)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public function PayRequest(){
        $SingDate = $this->encrypt ($this->merchant_id.';'.$this->OrderId.';'.$this->Amount,$this->terminal_key);
        $data = [
            'MerchantID' =>  $this->merchant_id,
            'Amount' => $this->Amount ,
            'OrderId' => $this->OrderId ,
            'CallBackUrl' => $this->CallBackUrl,
            'SignData' => $SingDate,
            'OptionalData' => $this->OptionalData
        ];
        $json_data = json_encode($data);
        $FirstRequestToPayRiz = $this->CallAPI($this->PayRequestUrl,$json_data);
        $response = json_decode ($FirstRequestToPayRiz);
        // dd ($response);
        if($response->Status == 1) // If FirstRequestToPayRiz Status == true Redirect Payment Page
        {
            $FirstQueryOnDataBase = DB::table('gateway_transactions')
                ->where('OrderId' , $this->OrderId)
                ->first();
//            dd($FirstQueryOnDataBase);
            if (empty($FirstQueryOnDataBase)){
                    DB::table ('gateway_transactions')->insert ([
                        'OrderId' => $this->OrderId,
                        'Amount' => $this->Amount,
                        'note' => 'کاربر به صفحه پرداخت هدایت شد. منتظر پاسخ از بانک',
                        'created_at' => Carbon::create (),
                        'updated_at' => Carbon::create ()
                    ]);
            }
            else{
                DB::table ('gateway_transactions')
                    ->where('OrderId' , $this->OrderId)
                    ->update ([
                    'OrderId' => $this->OrderId,
                    'Amount' => $this->Amount,
                    'note' => 'کاربر برای چندمین بار به صفحه پرداخت هدایت شد. منتظر پاسخ از بانک',
                    'created_at' => Carbon::create (),
                    'updated_at' => Carbon::create ()
                ]);
            }
            $Token = $response->Token;
            $url = "https://my.payriz.ir/?a=payment.purchase&Token=$Token";
            header("Location: {$url}");
            exit;
        }
        else{
            $responseMassaged = $this->PayRizError ($response->ErrorCode);
            return Response()->json (
               [
                   'Status' => 0,
                   'ErrorCode' => $response->ErrorCode,
                   'ErrorMessage' => $response->ErrorMessage,
                   'Help' => $responseMassaged
               ]);
        }
    }
    public function Verify(){
        $key = $this->terminal_key;
        // 1 - Receive Data
        $Token = \request ("Token");
        $OrderID = \request ("OrderId");
        $ResCode= \request ("Status");
        $response = null;
        // 2 - Request For Check Payment Status
        $verifyData = ['Token'=>$Token,'SignData'=>$this->encrypt($Token,$key)];
        $SingDate = json_encode($verifyData);
        $RequestToPayRiz = $this->CallAPI('https://my.payriz.ir/?a=payment.verify',$SingDate);
        $response = json_decode($RequestToPayRiz);

        if( $ResCode == 0 ) {
            if( $response->Status == 1) {
                $values = ['Status' => $response->Status , 'Amount' => $response->Amount ,'SystemTraceNo' =>
                $response->SystemTraceNo , 'RetrivalRefNo' => $response->RetrivalRefNo ];
                DB::table ('gateway_transactions')->where ('OrderId' , $OrderID)->update (
                    [
                        'SystemTraceNo' => $response->SystemTraceNo ,
                        'RetrivalRefNo' => $response->RetrivalRefNo ,
                        'ResCode' => $response->Status,
                        'note' => 'پرداخت انجام شد.',
                        'updated_at' => Carbon::create ()
                    ]
                );
                return true;
            }
            else{
                $pm = null;
                if (empty($response->ErrorMessage)){$pm = ' پی ریز پاسخگو پرداخت نیست ';}
                else{$pm = $response->ErrorMessage;}
                $values = ['Status' => $response->Status ,
                        'Description' => $pm ];
               DB::table('gateway_transactions')->where ('OrderId' , $OrderID)->update (
                   [
                       'note' => $pm,
                       'ResCode' => $response->Status,
                       'updated_at' => Carbon::create ()
                   ]
               );
                return false;
            }

        }
        else{
            $CheckDataOnDataBase = DB::table('gateway_transactions')->where ('OrderId' , $OrderID)->first();
            if (empty($CheckDataOnDataBase)){
                DB::table('gateway_transactions')->where ('OrderId' , $OrderID)->update (
                    [
                        'note' => 'پاسخ مناسبی برای این پرداخت یافت نشد.',
                        'ResCode' => '-20',
                        'updated_at' => Carbon::create ()
                    ]
                );
                return 0;
            }
            else{
                return 0;
            }
        }

    }
}
