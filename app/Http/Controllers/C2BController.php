<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class C2BController extends BuyAirtimeController
{
    public function log_stk($lmsg)
    {
        $flog = sprintf("/d/mira/chechi/training/laravel/C2B_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function Lipa()
    {
        $now = Carbon::now();

        $savedToken = DB::table('mpesa_token')
            ->orderByDesc('id')
            ->first();

        if (isset($savedToken))
        {
            $verification = $now->isAfter($savedToken->expires_in);

            if ($verification) {
                $token = $this->FreshOne();
            } else {
                $token = $savedToken->access_token;
            }
        }
        else
        {
            $token = $this->FreshOne();
        }

        $test = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';
        $prod ='https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        //$auth = Token();
        if (isset($token))
        {

            $accessToken = "Bearer ".$token;
            $shortcode ='600982';

            $ch = curl_init($test);
            curl_setopt($ch,  CURLOPT_HTTPHEADER,
                ['Authorization: '.$accessToken,
                'Content-Type: application/json'
                ]);

            $request = array(
                'ShortCode'=>$shortcode,
                'CommandID' =>'CustomerPayBillOnline',
                'Amount' => 10,
                'Msisdn' => '254707772715',
                'BillRefNumber' => 'TX23CS7Y'

            );

            $requestBody = json_encode($request);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);


            if($response === FALSE)
            {
                $resp ='Request Error:' . curl_error($ch);

                $this->log_stk($resp);

            }
            else
            {

                $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);

                switch($http_code)
                {
                    case "200":  # OK

                    $requestVals = json_decode($response, TRUE);
                    $ResponseCode = isset($requestVals['ResponseCode']) ? $requestVals['ResponseCode'] : '';
                    $OriginatorCoversationID = isset($requestVals['OriginatorCoversationID']) ? $requestVals['OriginatorCoversationID'] : '';
                    $ResponseDescription = isset($requestVals['ResponseDescription']) ? $requestVals['ResponseDescription'] : '';


                    //  $resp = DB::table('mpesa_txn')->insert([
                    //         'MerchantRequestID' => $MerchantRequestID,
                    //         'CheckoutRequestID' => $CheckoutRequestID,
                    //         'ResponseCode'=> $ResponseCode,
                    //         'ResponseDescription' => $ResponseDescription,
                    //         'CustomerMessage' => $CustomerMessage,
                    //         'PhoneNumber' => '254'.$msisdn,
                    //         'Amount' => $amount

                    //     ]);
                    $this->log_stk($requestVals);

                    $message = $ResponseDescription;
                    $status = "info";
                    return redirect()->route('buy_airtime')->with(['msg' => $message,'state' =>$status]);

                    break;

                    default:
                    $data = json_decode($response);
                    $requestId=$data->requestId;
                    $errorCode=$data->errorCode;
                    $errorMessage=$data->errorMessage;
                    $resp="|HTTP_CODE: ".$http_code."|errorCode: ".$errorCode."|message: ".$errorMessage;

                    $res = DB::table('mpesa_txn')->insert([
                        'requestId' => $requestId,
                        'errorCode' => $errorCode,
                        'errorMessage' => $errorMessage

                    ]);

                    $this->log_stk($res);

                    $message = $errorMessage;
                    $status = "danger";
                    return redirect()->route('buy_airtime')->with(['msg' => $message,'state' =>$status]);

                    break;

                }
            }


            curl_close($ch);
        }
        return $resp;

    }


}

