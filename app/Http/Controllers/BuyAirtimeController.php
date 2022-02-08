<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class BuyAirtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::check())
        {
            $user = auth()->user()->username;

            $table = DB::table('mpesa_txn')

                ->orderBy('created_at','DESC')
                ->get();

                // ->chunk(100, function($rows){});

            return view('pages.buy_airtime',['table' => $table]);
        }
        //$message = "Session timeout!";
        return redirect()->route('login');
    }

    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function purchase(Request $request)
    {
        $request->validate([
            'phone' => 'required |regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
            'amount' => 'required'
        ]);

        $data = $request->all();
        $phone = $request['phone'];
        $amount = $request['amount'];
        $MpesaReceiptNumber = $request['MpesaReceiptNumber'];
        $now = Carbon::now();

        $msisdn = $this->phoneNumber($phone);

        $savedToken = DB::table('air_token')
            ->orderByDesc('id')
            ->first();

        if (isset($savedToken)) {
            $verification = $now->isAfter($savedToken->expires_in);

            if ($verification) {
                $token = $this->getFreshOne();
            } else {
                $token = $savedToken->access_token;
            }
        } else {
            $token = $this->getFreshOne();
        }

        $test = 'https://sandbox.safaricom.co.ke/v1/pretups/api/recharge';
        $prod = 'https://prod.safaricom.co.ke/v1/pretups/api/recharge';

        //$auth = Token();
        if (isset($token)) {

            $accessToken = "Bearer ".$token;
            $pin = base64_encode('9090');
            $amt = (int)$amount*100;

            $ch = curl_init($test);
            curl_setopt($ch,  CURLOPT_HTTPHEADER,
                ['Authorization: '.$accessToken,
                'Content-Type: application/json'
                ]);

            $request = '{
                "senderMsisdn":"254748248717",
                "amount":"'.$amt.'",
                "servicePin":"'.$pin.'",
                "receiverMsisdn":"'.$msisdn.'"
                }';

            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);


            if(curl_errno($ch))
            {
                $resp ='Request Error:' . curl_error($ch);

                $this->log_this($resp);

            }
            else
            {
                $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
                switch($http_code)
                {
                    case "200":  # OK
                    $data = json_decode($response);
                    $responseId=$data->responseId;
                    $responseDesc=$data->responseDesc;
                    $responseStatus =$data->responseStatus;
                    $transId=$data->transId;
                    $resp="HTTP_CODE: ".$http_code."|ResponseID: ".$responseId."|Status: ".$responseStatus."|TransactionID: ".$transId."|Message: ".$responseDesc;
                    $this->log_this($resp);

                    DB::table('air_txn')->insert([
                        'responseId' => $responseId,
                        'responseStatus' => $http_code,
                        'responseDesc' => $responseDesc,
                        'receiverMsisdn' => $msisdn,
                        'amount' => $amount,
                        'transId' => $transId
                    ]);

                    DB::table('purchase')
                        ->where('mpesaReceipt', $MpesaReceiptNumber)
                        ->limit(1)
                        ->update([
                            'astatus' => $responseStatus,
                            'PhoneNumber' => $msisdn,
                            'transId' => $transId
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                        ]);

                    $message = $responseDesc;
                    $status = "info";
                    return redirect()->route('buy_airtime')->with(['message' => $message,'status' =>$status]);

                    //  if($http_code === 200)
                    //  {
                    //    StkPush($msisdn,$amount,$service);
                    //  }
                    break;

                    default:
                    $data = json_decode($response);
                    $errorCode=$data->errorCode;
                    $resId=$data->responseId;
                    $errorMessage=$data->errorMessage;
                    $resp="|HTTP_CODE: ".$http_code."|errorCode: ".$errorCode."|message: ".$errorMessage;
                    $this->log_this($resp);

                    DB::table('air_txn')->insert([
                        'responseId' => $resId,
                        'responseStatus' => $http_code,
                        'responseDesc' => $errorMessage,
                        'receiverMsisdn' => $msisdn,
                        'amount' => $amount,
                        'transId' => 'NA'
                    ]);
                    $message = $errorMessage;
                    $status = "danger";
                    return redirect()->route('buy_airtime')->with(['message' => $message,'status' =>$status]);

                    break;
                }
            }

            curl_close($ch);
    }

    }
    public function phoneNumber($msisdn)
    {
        $justNums = preg_replace("/[^0-9]/", '', $msisdn);

            $justNums = preg_replace("/^0/", '',$justNums);

            return $justNums;

    }

    private function getFreshOne()
    {
        $token = null;
        $tokenResult = $this->generateAccessToken();
        $accessVals = json_decode($tokenResult, TRUE);
        if ($accessVals['status'] == 1) {
            $token = $accessVals['access_token'];

            $this->saveToken($token);
        }
        return $token;
    }

    private function saveToken($tokenToSave)
    {
        $time = Carbon::now()
            ->addMinutes(50)
            ->format('Y-m-d H:i:s');

        DB::table('air_token')->insert([
                'access_token' => $tokenToSave,
                'expires_in' => $time,
                'status' => 0
            ]);
    }

    function generateAccessToken()
    {
        $accessToken = "";
        $status = 0;
        $description = "";

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $prod = 'https://api.safaricom.co.ke/oauth2/v3/generate?grant_type=client_credentials';

        //$auth = base64_encode('AosKfIeqa8WPx2L9MbPyAfs6tLvZgVYM:CsiCaAPwLYyaSWUp');
        $auth ='cFJZcjZ6anEwaThMMXp6d1FETUxwWkIzeVBDa2hNc2M6UmYyMkJmWm9nMHFRR2xWOQ==';

        $curl = curl_init($url);
        curl_setopt($curl,  CURLOPT_HTTPHEADER,
            [
                'Authorization: Basic '.$auth,
                'Content-Type: application/json; charset=utf8',
                'Accept-Language: EN'
            ]);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $curl_response = curl_exec($curl);

        if ($curl_response != FALSE) {
            $responseVals = json_decode($curl_response, TRUE);

            $responseVals = json_decode($curl_response, TRUE);

            $accessToken = $responseVals['access_token'];
            $status = 1;
        } else {
            $description = "Curl Failed: " . curl_error($curl);
            $message = $description;
            $status = "danger";
            return redirect()->route('buy_airtime')->with(['message' => $message,'status' =>$status]);

        }

        $array = array('status' => $status, 'access_token' => $accessToken, 'description' => $description);

        $message = $array;
        $status = "danger";
        ///return redirect()->route('buy_airtime')->with(['message' => $message,'status' =>$status]);

        return json_encode($array);
    }

    // STK PUSH TO CUSTOMER

    public function log_stk($lmsg)
    {
        $flog = sprintf("/var/log/popsms/stkpush_2_customer%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function stkpush(Request $request)
    {
        $request->validate([
            'number' => 'required |regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10',
            'pesa' => 'required'
        ]);

        $phone = $request['number'];
        $amount = $request['pesa'];
        $now = Carbon::now();

        $msisdn = $this->Number($phone);

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

        $test = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $prod ='https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        //$auth = Token();
        if (isset($token))
        {

            $accessToken = "Bearer ".$token;
            $service ="Airtime";
            $shortcode ='174379';
            $passkey ='bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
            $timestamp = date('YmdHis');
            $password = base64_encode($shortcode.$passkey.$timestamp);

            $ch = curl_init($test);
            curl_setopt($ch,  CURLOPT_HTTPHEADER,
                ['Authorization: '.$accessToken,
                'Content-Type: application/json'
                ]);
            //$end = 'https://164.90.133.19:1010/stk/resp.php';
            //$end2 = 'https://164.90.133.19:4040/api/resp/stk';
            $request = array(
                'BusinessShortCode'=>$shortcode,
                'Password' =>$password,
                'Timestamp' =>$timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int)$amount,
                'PartyA' => $msisdn,
                'PartyB' => $shortcode,
                'PhoneNumber' => '254'.$msisdn,
                'CallBackURL' => 'https://164.90.133.19:4040/api/resp/stk',
                'AccountReference' => 'EasyTopup',
                'TransactionDesc' => $service
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
                    $MerchantRequestID = isset($requestVals['MerchantRequestID']) ? $requestVals['MerchantRequestID'] : '';
                    $CheckoutRequestID = isset($requestVals['CheckoutRequestID']) ? $requestVals['CheckoutRequestID'] : '';
                    $ResponseCode = isset($requestVals['ResponseCode']) ? $requestVals['ResponseCode'] : '';
                    $ResponseDescription = isset($requestVals['ResponseDescription']) ? $requestVals['ResponseDescription'] : 'No Response';
                    $CustomerMessage = isset($requestVals['CustomerMessage']) ? $requestVals['CustomerMessage'] : '';

                    if ($ResponseCode == '0')//success
                    {
                        $status = 1;
                    }

                     $resp = DB::table('mpesa_txn')->insert([
                            'MerchantRequestID' => $MerchantRequestID,
                            'CheckoutRequestID' => $CheckoutRequestID,
                            'ResponseCode'=> $ResponseCode,
                            'ResponseDescription' => $ResponseDescription,
                            'CustomerMessage' => $CustomerMessage,
                            'PhoneNumber' => '254'.$msisdn,
                            'Amount' => $amount

                        ]);

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

    }
    public function Number($msisdn)
    {
        $justNums = preg_replace("/[^0-9]/", '', $msisdn);

            $justNums = preg_replace("/^0/", '',$justNums);

            return $justNums;
    }

    private function FreshOne()
    {
        $token = null;
        $tokenResult = $this->AccessToken();
        $accessVals = json_decode($tokenResult, TRUE);
        if ($accessVals['status'] == 1) {
            $token = $accessVals['access_token'];

            $this->Token($token);
        }
        return $token;
    }

    private function Token($tokenToSave)
    {
        $time = Carbon::now()
            ->addMinutes(50)
            ->format('Y-m-d H:i:s');

        DB::table('mpesa_token')->insert([
                'access_token' => $tokenToSave,
                'expires_in' => $time,
                'status' => 0
            ]);
    }

    function AccessToken()
    {
        $accessToken = "";
        $status = 0;
        $description = "";

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $prod = 'https://api.safaricom.co.ke/oauth2/v3/generate?grant_type=client_credentials';

        $auth = base64_encode('mPxf8stqVzAQZfp91hS6nsYyESiRGLwq:DKHZWt0tjWfGPnC8');


        $curl = curl_init($url);
        curl_setopt($curl,  CURLOPT_HTTPHEADER,
            [
                'Authorization: Basic '.$auth,
                'Content-Type: application/json; charset=utf8'
            ]);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $curl_response = curl_exec($curl);

        if ($curl_response != FALSE) {
            $responseVals = json_decode($curl_response, TRUE);

            $responseVals = json_decode($curl_response, TRUE);

            $accessToken = $responseVals['access_token'];
            $status = 1;
        } else {
            $description = "Curl Failed: " . curl_error($curl);
            $message = $description;
            $status = "danger";
            return redirect()->route('buy_airtime')->with(['message' => $message,'status' =>$status]);

        }

        $array = array('status' => $status, 'access_token' => $accessToken, 'description' => $description);

        return json_encode($array);
    }

    public function callback(Request $request)
    {
        //when success
        $data = json_encode($request->all());

        $req = json_decode($data);

        $ResultCode=$req->Body->stkCallback->ResultCode;

        if($ResultCode == '0')
        {

            $MerchantRequestID = $req->Body->stkCallback->MerchantRequestID;
            $CheckoutRequestID = $req->Body->stkCallback->CheckoutRequestID;
            $ResultDesc = $req->Body->stkCallback->ResultDesc;
            $amount = $req->Body->stkCallback->CallbackMetadata->Item[0]->Value;
            $MpesaReceiptNumber = $req->Body->stkCallback->CallbackMetadata->Item[1]->Value;
            $TransactionDate = $req->Body->stkCallback->CallbackMetadata->Item[3]->Value;
            $PhoneNumber = $req->Body->stkCallback->CallbackMetadata->Item[4]->Value;
             //$Balance = $req->Body->stkCallback->CallbackMetadata->Item[2]->Value;


            $resp = "Merchant_req: ".$MerchantRequestID. " |Amount: ".$amount." MpesaReceiptNumber: ".$MpesaReceiptNumber." TransactionDate: ".$TransactionDate." PhoneNumber: ".$PhoneNumber;
            DB::table('mpesa_txn')
                    ->where('MerchantRequestID', $MerchantRequestID)
                    ->limit(1)
                    ->update([
                        'MerchantRequestID' => $MerchantRequestID,
                        'CheckoutRequestID' => $CheckoutRequestID,
                        'ResultCode' => $ResultCode,
                        'ResultDesc' => $ResultDesc,
                        'Amount' => $amount,
                        'MpesaReceiptNumber' => $MpesaReceiptNumber,
                        'TransactionDate' => $TransactionDate,
                        'PhoneNumber' => $PhoneNumber,
                        'Balance' => 'NA'
                    ],
                    [
                        'MerchantRequestID' => $MerchantRequestID,
                        'CheckoutRequestID' => $CheckoutRequestID
                    ]);

            DB::table('purchase')->insertOrIgnore([
                'mpesaReceipt' => $MpesaReceiptNumber,
                'amount' => $amount,
                'mstatus'=> $ResultCode,
                'msisdn' => $PhoneNumber
            ]);



             $phone = $PhoneNumber;
             $this->airtime($amount,$phone,$MpesaReceiptNumber);

            $this->log_stk("RESP: ".$resp);
        }
        else
        {
            $MerchantRequestID = $req->Body->stkCallback->MerchantRequestID;
            $CheckoutRequestID = $req->Body->stkCallback->CheckoutRequestID;
            $ResultDesc = $req->Body->stkCallback->ResultDesc;

            DB::table('mpesa_txn')
                    ->where('MerchantRequestID', $MerchantRequestID)
                    ->limit(1)
                    ->update([
                        'MerchantRequestID' => $MerchantRequestID,
                        'CheckoutRequestID' => $CheckoutRequestID,
                        'ResultCode' => $ResultCode,
                        'ResultDesc' => $ResultDesc

                    ],
                    [
                        'MerchantRequestID' => $MerchantRequestID,
                        'CheckoutRequestID' => $CheckoutRequestID
                    ]
                );

            $this->log_stk($data);

        }

    }

    public function airtime($amount,$phone,$MpesaReceiptNumber)
    {
        $msisdn = $this->phoneNumber($phone);
        $now = Carbon::now();

        $savedToken = DB::table('air_token')
            ->orderByDesc('id')
            ->first();

        if (isset($savedToken)) {
            $verification = $now->isAfter($savedToken->expires_in);

            if ($verification) {
                $token = $this->getFreshOne();
            } else {
                $token = $savedToken->access_token;
            }
        } else {
            $token = $this->getFreshOne();
        }

        $test = 'https://sandbox.safaricom.co.ke/v1/pretups/api/recharge';
        $prod = 'https://prod.safaricom.co.ke/v1/pretups/api/recharge';

        //$auth = Token();
        if (isset($token)) {

            $accessToken = "Bearer ".$token;
            $pin = base64_encode('9090');
            $amt = (int)$amount*100;

            $ch = curl_init($test);
            curl_setopt($ch,  CURLOPT_HTTPHEADER,
                ['Authorization: '.$accessToken,
                'Content-Type: application/json'
                ]);

            $request = '{
                "senderMsisdn":"254748248717",
                "amount":"'.$amt.'",
                "servicePin":"'.$pin.'",
                "receiverMsisdn":"'.$msisdn.'"
                }';

            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);


            if(curl_errno($ch))
            {
                $resp ='Request Error:' . curl_error($ch);

                $this->log_this($resp);

            }
            else
            {
                $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
                switch($http_code)
                {
                    case "200":  # OK
                    $data = json_decode($response);
                    $responseId=$data->responseId;
                    $responseDesc=$data->responseDesc;
                    $responseStatus =$data->responseStatus;
                    $transId=$data->transId;
                    $resp="HTTP_CODE: ".$http_code."|ResponseID: ".$responseId."|Status: ".$responseStatus."|TransactionID: ".$transId."|Message: ".$responseDesc;
                    $this->log_this($resp);

                    DB::table('air_txn')->insert([
                        'responseId' => $responseId,
                        'responseStatus' => $http_code,
                        'responseDesc' => $responseDesc,
                        'receiverMsisdn' => $msisdn,
                        'amount' => $amount,
                        'transId' => $transId
                    ]);

                    DB::table('purchase')
                        ->where('mpesaReceipt', $MpesaReceiptNumber)
                        ->limit(1)
                        ->update([
                            'astatus' => $responseStatus,
                            'PhoneNumber' => $msisdn,
                            'transId' => $responseId
                        ],
                        [
                            'transId' => $responseId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                        ]);

                    break;

                    default:
                    $data = json_decode($response);
                    $errorCode=$data->errorCode;
                    $resId=$data->responseId;
                    $errorMessage=$data->errorMessage;
                    $resp="|HTTP_CODE: ".$http_code."|errorCode: ".$errorCode."|message: ".$errorMessage;
                    $this->log_this($resp);

                    DB::table('air_txn')->insert([
                        'responseId' => $resId,
                        'responseStatus' => $http_code,
                        'responseDesc' => $errorMessage,
                        'receiverMsisdn' => $msisdn,
                        'amount' => $amount,
                        'transId' => 'NA'
                    ]);

                    break;
                }
            }

            curl_close($ch);
    }
    }

}
