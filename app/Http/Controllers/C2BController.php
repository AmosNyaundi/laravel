<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class C2BController extends BuyAirtimeController
{

    public function log_stk($lmsg)
    {
        $flog = sprintf("/var/log/popsms/C2B%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function lipa(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

        $TransType=$request->TransactionType;
        $MpesaReceiptNumber=$request->TransID;
        $Time=$request->TransTime;
        $amount=$request->TransAmount;
        $phone=$request->BillRefNumber;
        $balance=$request->OrgAccountBalance;
        $sender=$request->MSISDN;
        $invoice=$request->InvoiceNumber;
        $FName=$request->FirstName;
        $MName=$request->MiddleName;
        $LName=$request->LastName;

        DB::table('mpesa_txn')
            ->insert([
                'MerchantRequestID' => $TransType,
                'CheckoutRequestID' => $invoice,
                'ResultCode' => 0,
                'ResultDesc' => $FName .' '.$MName.' '.$LName,
                'Amount' => $amount,
                'MpesaReceiptNumber' => $MpesaReceiptNumber,
                'TransactionDate' => $Time,
                'PhoneNumber' => $sender,
                'Balance' => $balance
            ]);

        DB::table('purchase')->insertOrIgnore([
            'mpesaReceipt' => $MpesaReceiptNumber,
            'amount' => $amount,
            'mstatus'=> 0,
            'msisdn' => $sender
        ]);

        $this->kredo($amount,$phone,$MpesaReceiptNumber);


        // $response = '{
        //     "TransactionType":"Pay Bill",
        //     "TransID":"QBP6OW9CTA",
        //     "TransTime":"20220225011523",
        //     "TransAmount":"1.00",
        //     "BusinessShortCode":"4040333",
        //     "BillRefNumber":"angisa",
        //     "InvoiceNumber":"0",
        //     "OrgAccountBalance":"24.00",
        //     "ThirdPartyTransID":null,
        //     "MSISDN":"254707772715",
        //     "FirstName":"AMOS",
        //     "MiddleName":"NGISA",
        //     "LastName":"NYAUNDI"
        // }';

        // $phone = "707772715";
        // $amt = 10;
        // $time = time();
        // $transId = rand($time);
       //`curl -X POST "http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid=$transId&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge=$amt&mobileno=$phone&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0" `

    }

    public function kredo($amount,$phone,$MpesaReceiptNumber)
    {
        $msisdn = $this->phoneNumber($phone);
        $transId = Str::random(10);
        $transId = strtoupper($transId);


        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$msisdn.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POST, 1);

        // $result = curl_exec($ch);
        // if (curl_errno($ch)) {
        //     $error = 'Error:' . curl_error($ch);
        //     $this->log_this($error);
        // }
        // $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close($ch);

        // $this->log_this($result);
        $test = 'https://157.230.92.224:4835/call.php';
        $ch = curl_init($test);
        curl_setopt($ch,  CURLOPT_HTTPHEADER,
            [
            'Content-Type: application/json'
            ]);

        $request = '{
            "msisdn":"'.$msisdn.'",
            "transId":"'.$transId.'",
            "amount":"'.$amount.'",
            }';

        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->log_this($result);

        DB::table('purchase')
            ->where('mpesaReceipt', $MpesaReceiptNumber)
            ->limit(1)
            ->update([
                'astatus' =>  $http_code,
                'PhoneNumber' => $msisdn,
                'transId' => $transId
            ],
            [
                'transId' => $transId,
                'mpesaReceipt' => $MpesaReceiptNumber
            ]);

            DB::table('air_txn')->insert([
                'responseId' => "NA",
                'responseStatus' => $http_code,
                'responseDesc' => "Success",
                'receiverMsisdn' => $msisdn,
                'amount' => $amount,
                'transId' => $transId
            ]);
    }

    public function reversal(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

    }

    public function balance(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);

    }

    public function phoneNumber($msisdn)
    {
        $justNums = preg_replace("/[^0-9]/", '', $msisdn);

            $justNums = preg_replace("/^0/", '',$justNums);

            return $justNums;

    }


}

