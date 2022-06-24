<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgentsController extends Controller
{
    public function log_this($lmsg)
    {
        $flog = sprintf("/var/log/popsms/callback_agents_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_reg($lmsg)
    {
        $flog = sprintf("/var/log/popsms/register_agents_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function log_notify($lmsg)
    {
        $flog = sprintf("/var/log/popsms/notifyAgent_%s.log",date("Ymd-H"));
        $tlog = sprintf("\n%s%s",date("Y-m-d H:i:s T: ") , $lmsg);
        $f = fopen($flog, "a");
        fwrite($f,$tlog);
        fclose($f);
    }

    public function delivery(Request $request)
    {
        $dat = json_encode($request->all());
        $request = json_decode($dat);

        $requestId=$request->requestId;
        $requestTimeStamp=date_format(date_create($request->requestTimeStamp), 'Y-m-d H:i:s');
        $linkId=$request->requestParam->data[0]->value;
        $OfferCode=$request->requestParam->data[1]->value;
        $RefernceId=$request->requestParam->data[2]->value;
        $ClientTransactionId=$request->requestParam->data[3]->value;
        $Channel=$request->requestParam->data[5]->value;
        $Type=$request->requestParam->data[6]->value;
        $user_data=$request->requestParam->data[7]->value;
        $Msisdn=$request->requestParam->data[8]->value;
        $data=explode("#",$user_data);
        //$cid=$cr[0];
        $shortcode = 20750;
        $now = Carbon::now();
        $mwezi = $now->format('F');

        if (stripos($user_data, '#N') !== false)
        {
            DB::table('ondemand')
                ->insert([
                    'msisdn' => $Msisdn,
                    'message' => $user_data,
                    'shortcode' => $shortcode,
                    'offercode' => $OfferCode,
                    'linkid' => $linkId,
                    'requestid' => $requestId,
                    'clientTransactionId' => $ClientTransactionId,
                    'referenceId' => $RefernceId,
                    'requestTimeStamp' => $requestTimeStamp,
                    'created_at' => $now
                ]);

            $customer = $data[2];
            $justNums = preg_replace('/\D+/', '', $customer);
            $cus = "254".substr($justNums, -9);

            if(DB::table('agents')->where('phone', $Msisdn)->exists())
            {
                if(DB::table('nominated')->where('msisdn', $cus)->exists())
                {
                    $msg = "FAILED: The mobile number is already nominated!";
                    $this->log_reg($msg);
                    $this->bulk($Msisdn,$msg);
                }
                elseif(DB::table('agents')->where('phone', $cus)->exists())
                {
                    $msg = "FAILED: The mobile number is already registered as an agent!";
                    $this->log_reg($msg);
                    $this->bulk($Msisdn,$msg);
                }
                else
                {
                    $referr = DB::table('agents')
                            ->where('phone', $Msisdn)
                            ->first();
                    $agent_id = $referr->uniqueId;

                    DB::table('nominated')
                    ->insert([
                        'agent_id' => $agent_id,
                        'msisdn' => $cus,
                        'created_at' => $now
                    ]);

                    $this->log_reg("Nominated customer: ".$cus);
                    $msg = "Congratulations! You have nominated $cus. Buy airtime to any network paybill 4040333 n earn commission.";
                    $this->bulk($Msisdn,$msg);
                    $msg ="Conveniently Buy airtime for Safaricom, Airtel or Telkom via MPESA PAYBLL 4040333 even if you have fuliza. Enter your mobile number as your account number";
                    $Msisdn = $cus;
                    $this->bulk($Msisdn,$msg);
                }

            }
            else
            {
                $msg = "FAILED: You are not allowed to register agents(s). Call 0707772715 for assistance.";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
            }

        }
        elseif (stripos($user_data, '#R') !== false)
        {
            $mq= DB::table('ondemand')
                ->insert([
                    'msisdn' => $Msisdn,
                    'message' => $user_data,
                    'shortcode' => $shortcode,
                    'offercode' => $OfferCode,
                    'linkid' => $linkId,
                    'requestid' => $requestId,
                    'clientTransactionId' => $ClientTransactionId,
                    'referenceId' => $RefernceId,
                    'requestTimeStamp' => $requestTimeStamp,
                    'created_at' => $now
                ]);

            if($mq)
            {
                //$this->log_this("Successfully inserted into ondemand table");
                $this->log_this($dat);
            }
            else{
                $this->log_this("Failed to insert into ondemand table");
                $this->log_this($dat);
            }

            $ph = $data[2];
            $region = $data[3];

            $justNums = preg_replace('/\D+/', '', $ph);
            $phone = "254".substr($justNums, -9);

            if(DB::table('agents')->where('phone', $phone)->exists())
            {
                $msg = "FAILED: The mobile number is already registered!";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
            }
            elseif(DB::table('agents')->where('phone', $Msisdn)->exists())
            {
                $refer = DB::table('agents')
                        ->where('phone', $Msisdn)
                        ->first();
                $ref = $refer->uniqueId;

                $this->registerAgent($phone,$region,$ref,$Msisdn);
                $msg = "SUCCESS: You have registered $phone as an agent. Your agent ID is $ref";
                $this->bulk($Msisdn,$msg);
            }
            else
            {
                $msg = "FAILED: You are not allowed to register agents. Call: 0707772715 for assistance.";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
            }
        }
        elseif (stripos($user_data, '#BAL') !== false)
        {
            DB::table('ondemand')
                ->insert([
                    'msisdn' => $Msisdn,
                    'message' => $user_data,
                    'shortcode' => $shortcode,
                    'offercode' => $OfferCode,
                    'linkid' => $linkId,
                    'requestid' => $requestId,
                    'clientTransactionId' => $ClientTransactionId,
                    'referenceId' => $RefernceId,
                    'requestTimeStamp' => $requestTimeStamp,
                    'created_at' => $now
                ]);

            if(DB::table('agents')->where('phone', $Msisdn)->exists())
            {
                $agent = DB::table('agents')
                            ->where('phone', $Msisdn)
                            ->first();
                $agent_id = $agent->uniqueId;

                $com = DB::table('purchase')
                            ->where('uniqueId', $agent_id)
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->sum('amount');

                $commission = number_format($com*0.04, 2);

                $msg = "Your $mwezi commission is Ksh $commission. Sell more to earn more.";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
            }
            else
            {
                $msg = "Sorry! You are not allowed to use the service. Buy airtime for Safaricom, Airtel or Telkom via MPESA Pay bill 4040333 even if you have fuliza.";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
                exit();
            }
        }
        elseif (stripos($user_data, '#S') !== false)
        {
            $this->log_this($dat);
            DB::table('ondemand')
                ->insert([
                    'msisdn' => $Msisdn,
                    'message' => $user_data,
                    'shortcode' => $shortcode,
                    'offercode' => $OfferCode,
                    'linkid' => $linkId,
                    'requestid' => $requestId,
                    'clientTransactionId' => $ClientTransactionId,
                    'referenceId' => $RefernceId,
                    'requestTimeStamp' => $requestTimeStamp,
                    'created_at' => $now
                ]);

            if(DB::table('agents')->where('phone', $Msisdn)->exists())
            {
                $msg = "FAILED: The mobile number is already registered as an agent!";
                $this->log_reg($msg);
                $this->bulk($Msisdn,$msg);
            }
            else
            {
                if(DB::table('nominated')->where('msisdn', $Msisdn)->exists())
                {
                    //$res=DB::table('nominated')->find($Msisdn)->delete();
                    $res= DB::table('nominated')->where('msisdn', $Msisdn)->delete();
                    $this->log_reg("Deleted customer: ".$Msisdn);

                    if($res)
                    {
                        $agentId = mt_rand(1000,9999);
                        DB::table('agents')
                            ->insert([
                                'uniqueId' => $agentId,
                                'ref' => 'CH',
                                'phone' => $Msisdn,
                                'region' => 'NA',
                                'status' => 0,
                                'name' => 'Self Registration',
                            ]);

                        $msg = "Congratulations! Your Agent ID is $agentId. To check your commission, SMS EZ#BAL to 20750. Buy airtime to any network paybill 4040333 n earn commission.";
                        $this->bulk($Msisdn,$msg);
                        $this->log_reg($msg);

                    }
                }
                else
                {
                    $agentId = mt_rand(1000,9999);
                    DB::table('agents')
                        ->insert([
                            'uniqueId' => $agentId,
                            'ref' => $agentId,
                            'phone' => $Msisdn,
                            'region' => 'NA',
                            'status' => 0,
                            'name' => 'self Registration',
                        ]);

                    $msg = "Congratulations! Your Agent ID is $agentId. To check your commission, SMS EZ#BAL to 20750. Buy airtime to any network paybill 4040333 n earn commission.";
                    $this->bulk($Msisdn,$msg);
                    $this->log_reg($msg);
                }
            }

        }
        else
        {
            $msg = "FAILED: Invalid format. Call 0707772715 for assistance.";
            $this->log_reg($msg);
            $this->bulk($Msisdn,$msg);
        }
    }

    public function callback(Request $request)
    {
        $dat = json_encode($request->all());
        $request = json_decode($dat);

        /*
        $requestId=$request->requestId;
        $requestTimeStamp=date_format(date_create($request->requestTimeStamp), 'Y-m-d H:i:s');
        $linkId=$request->requestParam->data[0]->value;
        $OfferCode=$request->requestParam->data[1]->value;
        $RefernceId=$request->requestParam->data[2]->value;
        $ClientTransactionId=$request->requestParam->data[3]->value;
        $Channel=$request->requestParam->data[5]->value;
        $Type=$request->requestParam->data[6]->value;
        $user_data=$request->requestParam->data[7]->value;
        $Msisdn=$request->requestParam->data[8]->value;
        $data=explode("#",$user_data);
        //$cid=$cr[0];
        $phone = $data[1];
        $region = $data[2];
        $shortcode = 20750;
        $now = Carbon::now();

        $mq= DB::table('ondemand')
            ->insert([
                'msisdn' => $Msisdn,
                'message' => $user_data,
                'shortcode' => $shortcode,
                'offercode' => $OfferCode,
                'linkid' => $linkId,
                'requestid' => $requestId,
                'clientTransactionId' => $ClientTransactionId,
                'referenceId' => $RefernceId,
                'requestTimeStamp' => $requestTimeStamp,
                'created_at' => $now
            ]);

        if($mq)
        {
            $this->log_this("Successfully inserted into ondemand table");
            //$this->log_this($dat);
        }
        else{
            $this->log_this("Failed to insert into ondemand table");
            //$this->log_this($dat);
        }

        //$this->log_this($dat);

        if(DB::table('agents')->where('phone', $phone)->exists())
        {
            $msg = "FAILED: The mobile number is already registered!";
            $this->log_reg($msg);
            $this->bulk($Msisdn,$msg);
        }
        elseif(DB::table('agents')->where('phone', $Msisdn)->exists())
        {
            $refer = DB::table('agents')
                    ->where('phone', $Msisdn)
                    ->first();
            $ref = $refer->ref;

            $this->registerAgent($phone,$region,$ref,$Msisdn);
            $msg = "SUCCESS: You have nominated [$phone] as agent. Your agent code is [$ref]";
            $this->bulk($Msisdn,$msg);
        }
        else
        {
            $msg = "FAILED: You are not allowed to register agents. Call: 0707772715 for assistance.";
            $this->log_reg($msg);
            $this->bulk($Msisdn,$msg);
        }
        */

    }

    public function registerAgent($phone,$region,$ref,$Msisdn)
    {
        $agentId = mt_rand(1000,9999);
        DB::table('agents')
            ->insert([
                'uniqueId' => $agentId,
                'ref' => $ref,
                'phone' => $phone,
                'region' => ucfirst($region),
                'status' => 0,
                'name' => 'NA'
            ]);

        $msg = "Congratulations! You have been registered as an agent by $Msisdn. Your Agent ID is $agentId. Buy airtime to any network paybill 4040333 n earn commission.";
        $Msisdn = $phone;
        $this->bulk($Msisdn,$msg);
        $this->log_reg($msg);

    }

    public function bulk($Msisdn,$msg)
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

}
