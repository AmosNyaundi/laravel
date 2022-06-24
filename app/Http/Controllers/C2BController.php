<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class C2BController extends BuyAirtimeController
{

    public function log_stk($lmsg)
    {
        $flog = sprintf("/var/log/popsms/C2B_%s.log",date("Ymd-H"));
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

    public function log_reverse($lmsg)
    {
        $flog = sprintf("/var/log/popsms/reversal%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_error($lmsg)
    {
        $flog = sprintf("/var/log/popsms/error_recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_pin($lmsg)
    {
        $flog = sprintf("/var/log/popsms/PIN_recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_notify($lmsg)
    {
        $flog = sprintf("/var/log/popsms/notifySMS_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function lipa(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);
        $now = Carbon::now();

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

        $agent_id = $this->agent_id($sender);

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
                'Balance' => $balance,
                'created_at' => $now
            ]);

        DB::table('purchase')->insertOrIgnore([
            'mpesaReceipt' => $MpesaReceiptNumber,
            'amount' => $amount,
            'mstatus'=> 0,
            'msisdn' => $sender,
            'PhoneNumber' => $phone,
            'uniqueId' => $agent_id,
            'created_at' => $now
        ]);

        $justNums = preg_replace('/\D+/', '', $phone);

        if(strlen($justNums) == '11' )
        {
            $this->kplc($amount,$phone,$sender,$FName,$MpesaReceiptNumber);
        }
        else
        {
            $this->kredo($amount,$phone,$MpesaReceiptNumber,$sender,$FName);
        }

        // $response = '{
        //     "TransactionType":"Pay Bill",
        //     "TransID":"QBP6OW9CTA",
        //     "TransTime":"20220225011523",
        //     "TransAmount":"1.00",
        //     "BusinessShortCode":"4040333",
        //     "BillRefNumber":"0799248518",
        //     "InvoiceNumber":"0",
        //     "OrgAccountBalance":"24.00",
        //     "ThirdPartyTransID":null,
        //     "MSISDN":"254707772715",
        //     "FirstName":"AMOS",
        //     "MiddleName":"NGISA",
        //     "LastName":"NYAUNDI"
        // }';
    }

    public function kredo($amount,$phone,$MpesaReceiptNumber,$sender,$FName)
    {
        $now = Carbon::now();
        $msisdn = $this->phoneNumber($phone);
        $transId = "CHA".Str::random(10);
        $transId = strtoupper($transId);
        $circle = substr($msisdn, 0, 3);
        $code = $this->operator($circle);
        $agent_id = $this->agent_id($sender);

        if (isset($code))
        {
            $ch = curl_init();
            $headers = array();
            $headers[] = 'Content-Length: 0';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_etrans.php3?agentid=61&transid='.$transId.'&retailerid=15&operatorcode='.$code.'&circode=*&product&denomination=0&recharge='.$amount.'&mobileno='.$msisdn.'&bulkqty=1&narration=buy%20airtime&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $result = curl_exec($ch);
            $this->log_this($result);
            $balance = $this->pin_bal();

            $msg = "Dear $FName, Your airtime purchase request is being processed. SMS EZ#S to 20750 to earn commission. Customer Care 0707772715.";
            $Msisdn = $sender;
            $this->single($Msisdn,$msg);

            if (strpos($result, '#ERROR') !== false)
            {
                DB::table('purchase')
                ->where('mpesaReceipt', $MpesaReceiptNumber)
                ->limit(1)
                ->update([
                    'astatus' => 400,
                    'PhoneNumber' => '0'.$msisdn,
                    'transId' => $transId,
                    'operator' => $circle,
                    'reason' => $result
                ],
                [
                    'transId' => $transId,
                    'mpesaReceipt' => $MpesaReceiptNumber
                ]);

                $msg="Dear Amos, Transaction for $FName has failed with error: $result ";
                $Msisdn = '254707772715';
                $this->single($Msisdn,$msg);
            }
            else
            {
                if(DB::table('nominated')->where('msisdn', $sender)->exists())
                {
                    $num = DB::table('agents')
                                ->where('uniqueId', $agent_id)
                                ->first();
                    $agent_num = $num->phone;

                    $com = DB::table('purchase')
                                ->where('uniqueId', $agent_id)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->sum('amount');

                    $commission = number_format($com*0.04, 2);

                    $data = explode("%$", $result);
                    $merchanttransid = $data[0];
                    $pktransid =$data[1];
                    $transdatetime = $data[2];
                    $res = explode(".", $data[3]);
                    $responsecode = $res[1];
                    $responsemessage = trim($data[4],"[SUCCESS:200] ");
                    $status = trim($data[5],"$$$");

                    $msg="Your customer $sender has bought airtime of Ksh $amount. Your commission is Ksh $commission as at $now. Sell more to earn more.";
                    $Msisdn= $agent_num;
                    $this->single($Msisdn,$msg);

                    DB::table('purchase')
                        ->where('mpesaReceipt', $MpesaReceiptNumber)
                        ->limit(1)
                        ->update([
                            'astatus' =>  $responsecode,
                            'PhoneNumber' => '0'.$msisdn,
                            'transId' => $merchanttransid,
                            'operator' => $circle,
                            'reason' => $data,
                            'balance' => $balance
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                    ]);

                    DB::table('air_txn')->insert([
                        'responseId' => $pktransid,
                        'responseStatus' => $responsecode,
                        'responseDesc' => $responsemessage,
                        'receiverMsisdn' => '0'.$msisdn,
                        'senderMsisdn' => $sender,
                        'amount' => $amount,
                        'transId' => $merchanttransid
                    ]);

                }
                elseif(DB::table('agents')->where('phone', $sender)->where('ref', 'CH')->exists())
                {
                    $com = DB::table('purchase')
                                ->where('uniqueId', $agent_id)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->sum('amount');

                    $commission = number_format($com*0.04, 2);

                    $data = explode("%$", $result);
                    $merchanttransid = $data[0];
                    $pktransid =$data[1];
                    $transdatetime = $data[2];
                    $res = explode(".", $data[3]);
                    $responsecode = $res[1];
                    $responsemessage = trim($data[4],"[SUCCESS:200] ");
                    $status = trim($data[5],"$$$");
                    $msg="Your customer $sender has bought airtime of Ksh $amount. Your commission is Ksh $commission as at $now. Sell more to earn more.";
                    $Msisdn= $sender;
                    $this->single($Msisdn,$msg);

                    DB::table('purchase')
                        ->where('mpesaReceipt', $MpesaReceiptNumber)
                        ->limit(1)
                        ->update([
                            'astatus' =>  $responsecode,
                            'PhoneNumber' => '0'.$msisdn,
                            'transId' => $merchanttransid,
                            'operator' => $circle,
                            'reason' => $data,
                            'balance' => $balance
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                    ]);

                    DB::table('air_txn')->insert([
                        'responseId' => $pktransid,
                        'responseStatus' => $responsecode,
                        'responseDesc' => $responsemessage,
                        'receiverMsisdn' => '0'.$msisdn,
                        'senderMsisdn' => $sender,
                        'amount' => $amount,
                        'transId' => $merchanttransid
                    ]);
                }
                else
                {
                    $data = explode("%$", $result);
                    $merchanttransid = $data[0];
                    $pktransid =$data[1];
                    $transdatetime = $data[2];
                    $res = explode(".", $data[3]);
                    $responsecode = $res[1];
                    $responsemessage = trim($data[4],"[SUCCESS:200] ");
                    $status = trim($data[5],"$$$");

                    DB::table('purchase')
                        ->where('mpesaReceipt', $MpesaReceiptNumber)
                        ->limit(1)
                        ->update([
                            'astatus' =>  $responsecode,
                            'PhoneNumber' => '0'.$msisdn,
                            'transId' => $merchanttransid,
                            'operator' => $circle,
                            'reason' => $data,
                            'balance' => $balance
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                    ]);

                    DB::table('air_txn')->insert([
                        'responseId' => $pktransid,
                        'responseStatus' => $responsecode,
                        'responseDesc' => $responsemessage,
                        'receiverMsisdn' => '0'.$msisdn,
                        'senderMsisdn' => $sender,
                        'amount' => $amount,
                        'transId' => $merchanttransid
                    ]);
                }
            }

            if (curl_errno($ch))
            {
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $result = 'Error occured. ' . curl_error($ch);
                $this->log_this($result);
                $this->log_error($result);
                $this->bulk($sender,$result,$FName);

                DB::table('purchase')
                ->where('mpesaReceipt', $MpesaReceiptNumber)
                ->limit(1)
                ->update([
                    'astatus' =>  $http_code,
                    'transId' => $transId,
                    'reason' => $result
                ],
                [
                    'mpesaReceipt' => $MpesaReceiptNumber
                ]);

                $msg="Dear Amos, Transaction for $FName has failed with error: $result";
                $Msisdn = '254707772715';
                $this->single($Msisdn,$msg);
            }
        }
        else
        {
            $result ="The mobile number does not exist.";
            $this->bulk($sender,$result,$FName);

            DB::table('purchase')
                ->where('mpesaReceipt', $MpesaReceiptNumber)
                ->limit(1)
                ->update([
                    'astatus' => 400,
                    'transId' => $transId,
                    'reason' => $result
                ],
                [
                    'mpesaReceipt' => $MpesaReceiptNumber
                ]);
        }
    }

    public function kplc($amount,$phone,$sender,$FName,$MpesaReceiptNumber)
    {
        $msisdn = $this->phoneNumber($phone);
        $transId = "PIN".Str::random(10);
        $transId = strtoupper($transId);
        $meter = $phone;
        $number = $sender;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_trans.php3?agentid=61&transid='.$transId.'&retailerid=61&operatorcode=5&circode=*&product&denomination=0&recharge='.$amount.'&deviceno='.$meter.'&mobileno='.$number.'&bulkqty=1&narration=buytoken&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = 'Content-Length: 0';
        $headers[] = 'Accept: plain/text';
        $headers[] = 'Content-Type: plain/text';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $this->log_pin($result);
        $balance = $this->pin_bal();

        if (strpos($result, '#ERROR') !== false)
        {
            DB::table('purchase')
                ->where('mpesaReceipt', $MpesaReceiptNumber)
                ->limit(1)
                ->update([
                    'astatus' => 400,
                    'PhoneNumber' => $phone,
                    'transId' => $transId,
                    'operator' => 'KPLC',
                    'reason' => $result
                ],
                [
                    'transId' => $transId,
                    'mpesaReceipt' => $MpesaReceiptNumber
                ]);

            $msg="Dear Amos, Transaction for $FName has failed with error: $result ";
            $Msisdn = '254707772715';
            $this->single($Msisdn,$msg);
        }
        else
        {
            //AFT2102022%$217514287758%$PDSLOVS31947401%$2022-06-24 14:28:02%$57330816338364592295%$3.14%$%$0%$A7.elec000%$[SUCCESS:elec000] Transaction Successful.%$SUCCESS$$$
            $data = explode("%$", $result);
            $merchanttransid = $data[0];
            $pktransid =$data[2];
            $date = $data[3];
            $tok = $data[4];
            $units = $data[5];
            $myarray = str_split($tok, 4);
            $token = implode("-", $myarray);

            $msg =  "KPLC Token". PHP_EOL .
                    "Meter: $meter". PHP_EOL .
                    "Token: $token". PHP_EOL .
                    "Amount: $amount". PHP_EOL .
                    "Units: $units". PHP_EOL .
                    "Date: $date". PHP_EOL .
                    "Thank You.";
            $Msisdn= $sender;
            $this->single($Msisdn,$msg);

            DB::table('purchase')
                ->where('mpesaReceipt', $MpesaReceiptNumber)
                ->limit(1)
                ->update([
                    'astatus' =>  200,
                    'PhoneNumber' => $msisdn,
                    'transId' => $merchanttransid,
                    'operator' => 'KPLC',
                    'reason' => $data,
                    'balance' => $balance
                ],
                [
                    'transId' => $transId,
                    'mpesaReceipt' => $MpesaReceiptNumber
            ]);

        }

        if (curl_errno($ch))
        {
            $msg = 'Error: ' . curl_error($ch);
            $this->log_pin($msg);
            $this->bulk($sender,$result,$FName);
            $Msisdn = $sender;
            $this->single($Msisdn,$msg);
        }
        curl_close($ch);

    }

    public function reversal(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_reverse($data);

    }

    public function balance(Request $request)
    {

        $data = json_encode($request->all());
        $this->log_stk($data);
    }

    public function phoneNumber($phone)
    {
        $justNums = preg_replace('/\D+/', '', $phone);
        $msisdn =substr($justNums, -9);
        return $msisdn;
    }

    public function operator($circle)
    {
        $airtel =array(10,104,730,731,732,733,734,735,736,737,738,739,750,751,752,753,754,755,756,762,780,781,782,783,784,785,786,787,788,789,100,101,102);

		$safcom =array(700,701,702,703,704,705,706,707,708,709,710,711,712,713,714,715,716,717,718,719,720,721,722,723,724,725,726,727,728,729,740,741,742,743,745,746,748,757,758,759,768,769,790,791,792,793,794,795,796,797,798,799,110,111,112,115);//added more prefixes

        $telkom =array(208,770,771,772,773,774,775,776,777,778,779);

        $kplc = array();

        if (in_array($circle, $airtel))
        {
            $code ='1';
            return $code;
        }
        elseif (in_array($circle, $safcom))
        {
            $code ='4';
            return $code;
        }
        elseif (in_array($circle, $telkom))
        {
            $code ='2';
            return $code;
        }
        else
        {
            $resp= "The mobile number does not exist in our operators";
            $this->log_error($resp);
            //return $resp
        }

    }

    public function pin_bal()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_query.php3?agentid=61&query=balance&loginstatus=LIVE&service=FLEXI&appver=1.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = 'Accept: plain/text';
        $headers[] = 'Content-Type: plain/text';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = 'Balance Error:' . curl_error($ch);
            $this->log_error($err);
        }

        $balance = trim($result,"$$$");
        return $balance;
        curl_close($ch);
    }

    public function bulk($sender,$msg,$FName)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mobilesasa.com/v1/send/bulk-personalized',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "senderID": "EAZYTOPUP",
            "messageBody": [
                {
                    "phone": '.$sender.',
                    "message": "Dear '.$FName.', Your airtime purchase request is being processed. Customer Care: 0707772715 / 0701324716 for assistance."
                },
                {
                    "phone": "254707772715",
                    "message": "Dear Amos, '.$msg.'."
                }
            ]

        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer NnmjJeifhmUWaXHHChzQgFtUe0KdYaRnEXSnRmlKABsbUAFe6YB6RINQDNsl'
        ),
        ));

        $response = curl_exec($curl);
        $this->log_notify($response);

        curl_close($curl);

    }

    public function single($Msisdn,$msg)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mobilesasa.com/v1/send/message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "senderID": "EAZYTOPUP",
            "message": "'.$msg.'",
            "phone": "'.$Msisdn.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer NnmjJeifhmUWaXHHChzQgFtUe0KdYaRnEXSnRmlKABsbUAFe6YB6RINQDNsl'
        ),
        ));

        $response = curl_exec($curl);
        $this->log_notify($response);

        curl_close($curl);

    }

    public function agent_id($sender)
    {
        if(DB::table('nominated')->where('msisdn', $sender)->exists())
        {
            $agent = DB::table('nominated')
                        ->where('msisdn', $sender)
                        ->first();
            $agent_id = $agent->agent_id;

            return $agent_id;
        }
        elseif(DB::table('agents')->where('phone', $sender)->where('ref', 'CH')->exists())
        {
            $agent = DB::table('agents')
                        ->where('phone', $sender)
                        ->first();
            $agent_id = $agent->uniqueId;

            return $agent_id;
        }
        else
        {
            $agent_id = 'CH';
            return $agent_id;
        }

    }

    public function merchant($amount,$phone,$sender,$FName,$MpesaReceiptNumber)
    {
        $msisdn = $this->phoneNumber($phone);
        $transId = "PIN".Str::random(10);
        $transId = strtoupper($transId);
        $circle = substr($msisdn, 0, 3);
        $code = $this->operator($circle);
        $now = Carbon::now();

        if($code == '1' || $code =='2' || $code == '4')
        {
            $denom = $amount;
            $meter = null;
            $number = null;
            $reason = "buyairtime";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://193.104.202.165/kenya/mainlinkpos/purchase/pw_trans.php3?agentid=61&transid='.$transId.'&retailerid=61&operatorcode='.$code.'&circode=*&product&denomination='.$denom.'&recharge='.$amount.'&deviceno='.$meter.'&mobileno='.$number.'&bulkqty=1&narration='.$reason.'&agentpwd=CHECHI123&loginstatus=LIVE&appver=1.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $headers = array();
            $headers[] = 'Content-Length: 0';
            $headers[] = 'Accept: plain/text';
            $headers[] = 'Content-Type: plain/text';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            $this->log_pin($result);
            $this->bulk($sender,$result,$FName);
        }
    }

}

