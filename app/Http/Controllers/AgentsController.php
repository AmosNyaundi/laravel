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
            //$this->log_this("Successfully inserted into ondemand table");
            $this->log_this($dat);
        }
        else{
            $this->log_this("Failed to insert into ondemand table");
            $this->log_this($dat);
        }

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
            $msg = "SUCCESS: You have nominated $phone as agent. Your agent ID is $ref";
            $this->bulk($Msisdn,$msg);
        }
        else
        {
            $msg = "FAILED: You are not allowed to register agents. Call: 0707772715 for assistance.";
            $this->log_reg($msg);
            $this->bulk($Msisdn,$msg);
        }
    }

    public function callback(Request $request)
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

    }

    public function registerAgent($phone,$region,$ref,$Msisdn)
    {
        $justNums = preg_replace('/\D+/', '', $phone);
        $msd =substr($justNums, -9);
        $agentId = mt_rand(1000,9999);
        DB::table('agents')
            ->insert([
                'uniqueId' => $agentId,
                'ref' => $ref,
                'phone' => '254'.$msd,
                'region' => ucfirst($region),
                'status' => 0,
                'name' => 'NA'
            ]);
        //$Msisdn = $phone;
        $msg = "Congratulations! You have been nominated as an agent by $Msisdn. Your AgentID is: $agentId. Click here to login: https://agents.eazytopup.co.ke/";
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
