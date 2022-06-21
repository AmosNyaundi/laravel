<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RetryController extends Controller
{
    public function api()
    {
        return view('pages.api_retry');
    }

    public function namba()
    {
        return view('pages.number_retry');
    }

    public function mfailure()
    {
        return view('pages.m_retry');
    }

    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/API_retry_recharge_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function api_retry(Request $request)
    {
        if(Auth::check())
        {
            auth()->user()->username;

            $request->validate([
                'mpesa_receipt' => 'required'
            ]);

            $MpesaReceiptNumber = $request['mpesa_receipt'];

            if(DB::table('purchase')->where('mpesaReceipt', $MpesaReceiptNumber)->where('astatus', '400')->exists())
            {
                $phone = DB::table('purchase')
                            ->where('mpesaReceipt', $MpesaReceiptNumber)
                            ->first();

                $air = DB::table('purchase')
                            ->where('mpesaReceipt', $MpesaReceiptNumber)
                            ->first();


                $justNums = preg_replace('/\D+/', '', $phone->PhoneNumber);
                $msisdn =substr($justNums, -9);
                // $cus = "254".$msisdn;
                // $agent = DB::table('nominated')
                //             ->where('phone', $cus)
                //             ->first();
                // $agent_id = $agent->agent_id;

                $transId = "CHA".Str::random(10);
                $transId = strtoupper($transId);
                $amount = $air->amount;

                $circle = substr($msisdn, 0, 3);
                $code = $this->operator($circle);

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
                    //$this->bulk($sender,$result,$FName);


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
                            'reason' => $result,
                            'balance' => $balance
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                        ]);

                        $message = $result;
                        $status = "danger";
                        return redirect()->route('api_retry')->with(['message' => $message,'status' =>$status]);
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
                        ///curl_close($ch);
                        //$this->bulk($sender,$result,$FName);
                        //$this->log_this($result);
                        //$balance = $this->pin_bal();

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

                        $message = $responsemessage;
                        $status = "info";
                        return redirect()->route('api_retry')->with(['message' => $message,'status' =>$status]);
                    }

                    if (curl_errno($ch))
                    {
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $result = 'Error occured. ' . curl_error($ch);
                        $this->log_this($result);
                        //$this->bulk($sender,$result,$FName);

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

                        $message = $result;
                        $status = "danger";
                        return redirect()->route('api_retry')->with(['message' => $message,'status' =>$status]);
                    }

                    /*
                    else
                    {
                        $data = explode("%$", $result);
                        $merchanttransid = $data[0];
                        //$pktransid =$data[1];
                        //$transdatetime = $data[2];
                        $res = explode(".", $data[3]);
                        $responsecode = $res[1];
                        $responsemessage = trim($data[4],"[SUCCESS:200] ");
                        $status = trim($data[5],"$$$");
                        ///curl_close($ch);
                        //$this->bulk($sender,$result,$FName);
                        //$this->log_this($result);
                        $balance = $this->pin_bal();

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

                        $message = $responsemessage;
                        $status = "info";
                        return redirect()->route('api_retry')->with(['message' => $message,'status' =>$status]);
                    } */

                }

            }
            else
            {

                $message = "No such failed transaction found";
                //$message = "Invalid transaction code";
                $status = "danger";
                return redirect()->route('api_retry')->with(['message' => $message,'status' =>$status]);
            }

        }
        else
        {
            return redirect()->route('login');
        }


    }

    public function namba_retry(Request $request)
    {
        if(Auth::check())
        {
            auth()->user()->username;

            $request->validate([
                'mpesa_receipt' => 'required',
                'number' => 'required |regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:10'
            ]);

            $MpesaReceiptNumber = $request['mpesa_receipt'];
            $phone = $request['number'];

            if(DB::table('purchase')->where('mpesaReceipt', $MpesaReceiptNumber)->where('astatus', '400')->exists())
            {
                $air = DB::table('purchase')->where('mpesaReceipt', $MpesaReceiptNumber)->first();
                $justNums = preg_replace('/\D+/', '', $phone);
                $msisdn =substr($justNums, -9);
                // $cus = "254".$msisdn;
                // $agent = DB::table('nominated')
                //             ->where('phone', $cus)
                //             ->first();
                // $agent_id = $agent->agent_id;

                $transId = "CHA".Str::random(10);
                $transId = strtoupper($transId);
                $amount = $air->amount;

                $circle = substr($msisdn, 0, 3);
                $code = $this->operator($circle);

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
                            'reason' => $result,
                            'balance' => $balance
                        ],
                        [
                            'transId' => $transId,
                            'mpesaReceipt' => $MpesaReceiptNumber
                        ]);

                        $message = $result;
                        $status = "danger";
                        return redirect()->route('namba_retry')->with(['message' => $message,'status' =>$status]);
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
                        ///curl_close($ch);
                        //$this->bulk($sender,$result,$FName);
                        //$this->log_this($result);
                        //$balance = $this->pin_bal();

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

                        $message = $responsemessage;
                        $status = "info";
                        return redirect()->route('namba_retry')->with(['message' => $message,'status' =>$status]);
                    }

                    if (curl_errno($ch))
                    {
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $result = 'Error occured. ' . curl_error($ch);
                        $this->log_this($result);
                        //$this->bulk($sender,$result,$FName);

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

                        $message = $result;
                        $status = "info";
                        return redirect()->route('namba_retry')->with(['message' => $message,'status' =>$status]);
                    }

                }

            }
            else
            {

                $message = "Invalid transaction code";
                $status = "danger";
                return redirect()->route('namba_retry')->with(['message' => $message,'status' =>$status]);
            }

        }
        else
        {
            return redirect()->route('login');
        }


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
            $resp= "The mobile number does not exist in our operators : ".$circle;
            $this->log_this($resp);
            $message = $resp;
            $status = "danger";
            return redirect()->route('number_retry')->with(['message' => $message,'status' =>$status]);
            //return $resp
        }

    }

    public function bulk($sender,$result,$FName)
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
        CURLOPT_POSTFIELDS =>'
        {
            "senderID": "EAZYTOPUP",
            "message": "Dear Amos, transaction '.$FName.' has been retried successfully.",
            "phone": "254707772715"


        }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer NnmjJeifhmUWaXHHChzQgFtUe0KdYaRnEXSnRmlKABsbUAFe6YB6RINQDNsl'
        ),
        ));

        $response = curl_exec($curl);
        //$this->log_notify($response);

        curl_close($curl);

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
            $this->log_this($err);
        }

        $balance = trim($result,"$$$");
        return $balance;
        curl_close($ch);
    }

}
