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

        $this->kredo($amount,$phone,$MpesaReceiptNumber,$sender);


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
    }

    public function kredo($amount,$phone,$MpesaReceiptNumber,$sender)
    {
        $msisdn = $this->phoneNumber($phone);
        $transId = "CHA".Str::random(10);
        $transId = strtoupper($transId);


        $ch = curl_init();
        $headers = array();
        $headers[] = 'Content-Length: 0';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode=4&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$msisdn.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = 'Request Failed::' . curl_error($ch);
            $this->log_this($error);
        }
        //$http_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = explode("%$", $result);
        $merchanttransid = $data[0];
        $pktransid =$data[1];
        $transdatetime = $data[2];
        $responsecode = trim($data[3],"A6.");
        $responsemessage = trim($data[4],"[SUCCESS:200]");
        $status = trim($data[5],"$$$");
        curl_close($ch);

        $this->log_this($result);

        DB::table('purchase')
            ->where('mpesaReceipt', $MpesaReceiptNumber)
            ->limit(1)
            ->update([
                'astatus' =>  $responsecode,
                'PhoneNumber' => $msisdn,
                'transId' => $merchanttransid
            ],
            [
                'transId' => $transId,
                'mpesaReceipt' => $MpesaReceiptNumber
            ]);

            DB::table('air_txn')->insert([
                'responseId' => $pktransid,
                'responseStatus' => $responsecode,
                'responseDesc' => $responsemessage,
                'receiverMsisdn' => $msisdn,
                'senderMsisdn' => $sender,
                'amount' => $amount,
                'transId' => $merchanttransid
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

